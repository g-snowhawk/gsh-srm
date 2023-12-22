<?php
/**
 * This file is part of G.Snowhawk Application.
 *
 * Copyright (c)2016 PlusFive (https://www.plus-5.com)
 *
 * This software is released under the MIT License.
 * https://www.plus-5.com/licenses/mit-license
 */

namespace Gsnowhawk\Srm\Receipt;

use Gsnowhawk\Common\Lang;
use Gsnowhawk\Common\Mail;

/**
 * Template management request response class.
 *
 * @license https://www.plus-5.com/licenses/mit-license  MIT License
 * @author  Taka Goto <www.plus-5.com>
 */
class Accept extends \Gsnowhawk\Srm\Receipt
{
    public const QUERY_STRING_KEY = 'receipt_accept_search_condition';
    public const SEARCH_OPTIONS_KEY = 'receipt_accept_search_options';
    public const RECEIPT_PAGE_KEY = 'receipt_accept_page';

    private $rows_per_page = 10;

    /**
     * Object Constructor.
     */
    public function __construct()
    {
        $params = func_get_args();
        call_user_func_array(parent::class.'::__construct', $params);

        if (is_null($this->view) && php_sapi_name() === 'cli') {
            $this->session->param('application_name', parent::packageName());
            $this->app->restoreView();
        }

        $this->view->bind(
            'header',
            ['title' => Lang::translate('HEADER_TITLE'), 'id' => 'template', 'class' => 'template']
        );
    }

    /**
     * Default view.
     */
    public function defaultView()
    {
        $this->checkPermission('srm.receipt.accept');

        $template_path = 'srm/receipt/accept.tpl';

        $receipt_name = null;
        if (!empty($this->request->param('t'))) {
            $receipt = $this->db->get('id,title', 'receipt_template', 'id = ?', [$this->request->param('t')]);
            if (!empty($receipt)) {
                $this->session->param('receipt_id', $receipt['id']);
                $receipt_name = $receipt['title'];

                // Reset current page number
                $this->session->param(self::RECEIPT_PAGE_KEY, 1);
            }
        }

        if (!empty($this->request->param('p'))) {
            $this->session->param(self::RECEIPT_PAGE_KEY, $this->request->param('p'));
        }

        $userkey = $this->getUserID($this->db);
        $groups = [];
        $hosts = [];
        $titles = [];
        if (false !== $this->db->query('SELECT id,userkey FROM table::group WHERE FIND_IN_SET(?, users)', [$userkey])) {
            $cln = clone $this->db;
            while ($unit = $this->db->fetch()) {
                $groups[] = $unit['id'];
                $userkey = $unit['userkey'];
                $company = $cln->get('company,fullname', 'user', 'id = ?', [$userkey]);
                $company['host'] = (empty($company['company'])) ? $company['fullname'] : $company['company'];
                $hosts[$userkey] = $company;

                // PDF mapping
                $templates = $cln->select('id,title,pdf_mapper', 'receipt_template', 'WHERE userkey = ?', [$userkey]);
                foreach ($templates as $template) {
                    $pdf_mapper = simplexml_load_string($template['pdf_mapper']);
                    $typeof = (string)$pdf_mapper->attributes()->typeof;
                    $titles[$userkey][$template['id']] = [
                        'title' => $template['title'],
                        'typeof' => $typeof,
                    ];
                }
            }
        }

        //$receipt_id = $this->session->param('receipt_id');
        //if (empty($receipt_id)) {
        //    $template_path = 'srm/receipt/receipt_list.tpl';
        //    $receipts = $this->db->select(
        //        'id,title',
        //        'receipt_template',
        //        'WHERE userkey = ? ORDER by priority',
        //        [$this->uid]
        //    );
        //    $this->view->bind('receipts', $receipts);
        //} else {
            //$type_of_receipt = null;
            //if (!empty($receipt_id)) {
            //    $receipt = $this->db->get('title,pdf_mapper,mail_template', 'receipt_template', 'id = ?', [$receipt_id]);
            //    $receipt_name = $receipt['title'];

            //    if (!empty($receipt['pdf_mapper'])) {
            //        $pdf_mapper = simplexml_load_string($receipt['pdf_mapper']);
            //        $type_of_receipt = (string)$pdf_mapper->attributes()->typeof;

            //        $duplicate_to = $pdf_mapper->duplicateto;
            //        $items = [];
            //        foreach ($duplicate_to->item as $item) {
            //            $items[] = [
            //                'id' => (string)$item->attributes()->id,
            //                'label' => (string)$item,
            //            ];
            //        }
            //        if (!empty($items)) {
            //            $this->view->bind('duplicateTo', $items);
            //        }
            //    }

            //    if (!empty($receipt['mail_template'])) {
            //        $this->view->bind('mail', 'enable');
            //    }
            //}

            //$this->view->bind('receiptName', $receipt_name);
            //$this->view->bind('typeOf', $type_of_receipt);

            //$collected = 'NULL';
            //if ($type_of_receipt === 'bill') {
            //    $collected = "CASE WHEN r.unavailable = '1' THEN 3
            //                       WHEN r.receipt IS NOT NULL THEN 3
            //                       WHEN r.receipt IS NULL AND r.due_date IS NULL THEN 2
            //                       WHEN r.receipt IS NULL AND DATE_FORMAT(r.due_date,'%Y-%m-%d 23:59:59') > NOW() THEN 2
            //                       ELSE 1
            //                   END";
            //}

            $shared = implode(',', array_fill(0, count($groups), '?'));
            $options = $groups;

            $search_options = $this->session->param(self::SEARCH_OPTIONS_KEY) ?: ['andor' => 'AND'];
            $between = '';
            if (!empty($search_options['issue_date_start'])) {
                $between .= ' AND r.issue_date >= ?';
                $options[] = date('Y-m-d', strtotime($search_options['issue_date_start']));
            }
            if (!empty($search_options['issue_date_end'])) {
                $between .= ' AND r.issue_date <= ?';
                $options[] = date('Y-m-d', strtotime($search_options['issue_date_end']));
            }
            $andor = (!empty($search_options['andor'])) ? $search_options['andor'] : 'AND';

            $query_string = $this->getSearchCondition();
            $receipt = 'table::receipt';
            $filter = '';
            include(__DIR__ . '/accept_statement.php');
            if (!empty($query_string)) {
                $keywords = explode(' ', $query_string);
                $filters = [];
                foreach ($keywords as $keyword) {
                    $filters[] = "%{$keyword}%";
                }
                $filter = implode(" {$andor} ", array_fill(0, count($filters), 'filter LIKE ?'));
                $options = array_merge($options, $filters);
                include(__DIR__ . '/accept_statement_search.php');

                $this->view->bind('queryString', $query_string);
                $this->session->param(self::QUERY_STRING_KEY, $query_string);
            }

            // Pagenation
            $current_page = (int)$this->session->param(self::RECEIPT_PAGE_KEY) ?: 1;
            $rows_per_page = (empty($this->session->param('rows_per_page_receipt_list')))
                ? $this->rows_per_page
                : (int)$this->session->param('rows_per_page_receipt_list');
            $total_count = $this->db->recordCount($statement, $options);
            $offset_list = $rows_per_page * ($current_page - 1);
            $pager = clone $this->pager;
            $pager->init($total_count, $rows_per_page);
            $pager->setCurrentPage($current_page);
            $pager->setLinkFormat($this->app->systemURI().'?mode='.parent::DEFAULT_MODE.'&p=%d');
            $this->view->bind('pager', $pager);
            $statement .= " LIMIT $offset_list,$rows_per_page";

            $this->db->prepare($statement);
            $this->db->execute($options);

            $receipts = [];
            while ($unit = $this->db->fetch()) {
                $unit['host'] = $hosts[$unit['userkey']]['host'];
                $unit['receipt_title'] = $titles[$unit['userkey']][$unit['templatekey']]['title'];
                $unit['receipt_type'] = $titles[$unit['userkey']][$unit['templatekey']]['typeof'];
                $receipts[] = $unit;
            }

            // Mail log
            //foreach ($receipts as &$receipt) {
            //    $logtime = $this->db->get(
            //        'logtime',
            //        'receipt_mail_log',
            //        'issue_date = ? AND receipt_number = ? AND userkey = ? AND templatekey = ? ORDER BY logtime DESC LIMIT 1',
            //        [$receipt['issue_date'], $receipt['receipt_number'], $this->uid, $receipt_id]
            //    );
            //    if (!empty($logtime)) {
            //        $receipt['logtime'] = $logtime;
            //    }
            //}
            //unset($receipt);

            $this->view->bind('receipts', $receipts);

            $this->setHtmlId('srm-receipt-accept');
            $this->appendHtmlClass('srm-receipt-list');

            $globals = $this->view->param();
            $form = $globals['form'];
            $form['confirm'] = Lang::translate('CONFIRM_DELETE_DATA');
            $this->view->bind('form', $form);
        //}

        $this->view->render($template_path);
    }


    public function downloadPdf()
    {
        if (!empty($this->request->GET('h'))) {
            $item = $this->db->get(
                'issue_date,receipt_number,userkey,templatekey',
                'receipt',
                'MD5(CONCAT(issue_date,receipt_number,userkey,templatekey,draft)) = ?',
                [$this->request->GET('h')]
            );
            $issue_date = $item['issue_date'];
            $receipt_number = $item['receipt_number'];
            $templatekey = $item['templatekey'];

            $pdf_mapper_source = $this->db->get('pdf_mapper', 'receipt_template', 'id = ? AND userkey = ?', [$templatekey, $item['userkey']]);
            if (empty($pdf_mapper_source)) {
                trigger_error('System Error', E_USER_ERROR);
            }

            $pdf_mapper = simplexml_load_string($pdf_mapper_source);

            $format = (string)$pdf_mapper->attributes()->savepath;
            $pdf_path = $this->pathToPdf($format, $issue_date, $receipt_number);

            if (!file_exists($pdf_path)) {
                trigger_error('PDF is not found', E_USER_ERROR);
            }

            $format = (string)$pdf_mapper->attributes()->download_name;
            $file_name = $this->pathToPdf($format, $issue_date, $receipt_number);

            header('Content-type: application/pdf');
            header("Content-Disposition: inline; filename*=UTF-8''".rawurlencode($file_name));
            readfile($pdf_path);
            exit;
        }
    }

    public function searchOptions(): void
    {
        $search_options = $this->session->param(self::SEARCH_OPTIONS_KEY) ?: ['andor' => 'AND'];
        $this->view->bind('post', $search_options);
        $response = $this->view->render('srm/receipt/accept_search_options.tpl', true);
        $json = [
            'status' => 200,
            'response' => $response,
        ];
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($json);
        exit;
    }

    private function getSearchCondition(): ?string
    {
        $query_string = (!$this->request->isset('q'))
            ? $this->session->param(self::QUERY_STRING_KEY)
            : $this->request->param('q');

        if (empty($query_string)) {
            $this->session->clear(self::QUERY_STRING_KEY);
            if (is_null($query_string)) {
                return null;
            }
        }

        return mb_convert_kana($query_string, 's');
    }

    //public function mailer(): void
    //{
    //    $json = ['status' => 0, 'headers' => []];
    //    if (preg_match("/^(\d{4}-\d{2}-\d{2}):(\d+)(:(\d+))?$/", $this->request->GET('id'), $match)) {
    //        $issue_date = $match[1];
    //        $receipt_number = $match[2];
    //        $templatekey = $this->session->param('receipt_id');

    //        $tmp = $this->db->get(
    //            'mail_template,pdf_mapper',
    //            'receipt_template',
    //            'id = ? AND userkey = ?',
    //            [$templatekey, $this->uid]
    //        );

    //        $mail_template = $tmp['mail_template'] ?? null;

    //        if (!empty($mail_template)) {
    //            $unit = $this->db->get(
    //                'issue_date,receipt_number,templatekey,client_id,subject,due_date',
    //                'receipt',
    //                'userkey = ? AND templatekey = ? AND issue_date = ? AND receipt_number = ?',
    //                [$this->uid, $templatekey, $issue_date, $receipt_number]
    //            );

    //            $unit['total'] = $this->totalOfReceipt($issue_date, $receipt_number, $templatekey);

    //            if (!empty($tmp['pdf_mapper'])) {
    //                $pdf_mapper = simplexml_load_string($tmp['pdf_mapper']);
    //                $format = (string)$pdf_mapper->attributes()->savepath;
    //                $pdf_path = $this->pathToPdf($format, $issue_date, $receipt_number);

    //                if (file_exists($pdf_path)) {
    //                    $format = (string)$pdf_mapper->attributes()->download_name;
    //                    $file_name = $this->pathToPdf($format, $issue_date, $receipt_number);
    //                    $json['pdf'] = [
    //                        'path' => $pdf_path,
    //                        'basename' => basename($pdf_path),
    //                        'size' => filesize($pdf_path),
    //                        'attachment_name' => $file_name,
    //                    ];
    //                }
    //            }

    //            $client = $this->db->get('company,fullname', 'receipt_to', 'id = ?', [$unit['client_id']]);
    //            $unit['company'] = $client['company'];
    //            $unit['fullname'] = $client['fullname'];

    //            $this->view->bind('unit', $unit);

    //            $template = $this->view->render($mail_template, true, true);

    //            if (preg_match('/^(((cc|from|reply-to|subject):.+?(\r\n|\r|\n)){1,})?(.+)$/is', $template, $match)) {
    //                $template = trim($match[5]);
    //                if (preg_match_all('/(cc|from|reply-to|subject):\s*(.+)/i', $match[1], $metas)) {
    //                    foreach ($metas[1] as $n => $value) {
    //                        $json['headers'][$value] = trim($metas[2][$n]);
    //                    }
    //                }
    //            }

    //            $json['template'] = $template;
    //            $json['token'] = $this->session->param('ticket');
    //            $json['headers']['issue_date'] = $unit['issue_date'];
    //            $json['headers']['receipt_number'] = $unit['receipt_number'];
    //            $json['headers']['templatekey'] = $unit['templatekey'];
    //        }
    //    }

    //    header('Content-type: application/json; charset=utf-8');
    //    echo json_encode($json);
    //    exit;
    //}

    /**
     * @todo separate by tax rate
     * @todo support to multiple pages
     *
     * @cli available
     */
    //public function remindBilling(): void
    //{
    //    if (php_sapi_name() !== 'cli') {
    //        trigger_error('Bad Requiest!', E_USER_ERROR);
    //    }

    //    $today = date('Y-m-d');
    //    $template = 'srm/receipt/remind_billing.tpl';
    //    $records = $this->db->select(
    //        '*',
    //        'receipt',
    //        'WHERE billing_date = ?'.
    //        'ORDER BY userkey, issue_date, receipt_number',
    //        [$today]
    //    );

    //    $data = [];
    //    $bill = [];
    //    foreach ($records as $record) {
    //        $client_fields = ['company','division','fullname','zipcode','address1','address2'];
    //        $client = $record['client_id'];
    //        $uname = $this->db->get('uname', 'user', 'id = ?', [$record['userkey']]);
    //        $this->resetUserByCli($uname);

    //        list($id, $mapper) = $this->receiptIdFromType('bill', true);
    //        $subject = $mapper->autoissue->subject ?? Lang::translate('AUTOISSUE_BILL_SUBJECT');
    //        $format = $mapper->autoissue->contentform ?? '%s (%tNo.%n)';
    //        $bill[$uname] = ['id' => $id, 'subject' => $subject];

    //        if (!isset($data[$uname])) {
    //            $data[$uname] = [];
    //        }
    //        if (!isset($data[$uname][$client])) {
    //            $data[$uname][$client] = [];
    //        }

    //        $total = $this->totalOfReceipt(
    //            $record['issue_date'],
    //            $record['receipt_number'],
    //            $record['templatekey'],
    //            '0',
    //            true
    //        );

    //        $record['title'] = $this->db->get('title', 'receipt_template', 'id = ?', [$record['templatekey']]);

    //        $data[$uname][$client][] = [
    //            'content' => $this->sprintf($format, $record),
    //            'price' => $total,
    //            'quantity' => 1,
    //            'issue_date' => $record['issue_date'],
    //            'receipt_number' => $record['receipt_number'],
    //            'templatekey' => $record['templatekey'],
    //        ];
    //    }

    //    $this->request->param('draft', '1');
    //    $this->request->param('note', '');

    //    $this->view->bind('billing_date', time());

    //    foreach ($data as $uname => $clients) {
    //        $this->resetUserByCli($uname);
    //        $this->session->param('receipt_id', $bill[$uname]['id']);

    //        $draft_bills = [];
    //        foreach ($clients as $client => $list) {
    //            $to = $this->db->get('*', 'receipt_to', 'id = ?', [$client]);
    //            if (empty($to)) {
    //                continue;
    //            }

    //            $client_name = $to['company'] ?? '';

    //            // Clear cached receipt_number
    //            $this->request->param('receipt_number', null, true);

    //            if (count($list) === 1) {
    //                $page_number = 1;

    //                if (false === $this->cloneReceipt(
    //                    $list[0]['templatekey'],
    //                    $list[0]['issue_date'],
    //                    $list[0]['receipt_number'],
    //                    $page_number,
    //                    $draft,
    //                    $bill[$uname]['id']
    //                )) {
    //                    trigger_error('Database Error.');
    //                } else {
    //                    $draft_bills[] = [
    //                        'number' => $this->clone_receipt_number,
    //                        'company' => $client_name,
    //                    ];
    //                }
    //                continue;
    //            }

    //            foreach ($client_fields as $field) {
    //                $this->request->param($field, ($to[$field] ?? ''));
    //            }

    //            $this->request->param('issue_date', $today);
    //            $this->request->param('subject', date($bill[$uname]['subject']));

    //            $line = 0;
    //            $content = [];
    //            $price = [];
    //            $quantity = [];

    //            foreach ($list as $attr) {
    //                ++$line;
    //                $content[$line] = $attr['content'];
    //                $price[$line] = $attr['price'];
    //                $quantity[$line] = $attr['quantity'];
    //                $unit[$line] = '';
    //            }

    //            $this->request->param('content', $content);
    //            $this->request->param('price', $price);
    //            $this->request->param('quantity', $quantity);
    //            $this->request->param('unit', $unit);

    //            if (false === $this->save()) {
    //                trigger_error('Database Error');
    //            } else {
    //                $draft_bills[] = [
    //                    'number' => $this->clone_receipt_number,
    //                    'company' => $client_name,
    //                ];
    //            }
    //        }

    //        $email = $this->userinfo['email'];
    //        if (!empty($email)) {
    //            $this->view->bind('draft_bills', $draft_bills);
    //            $eml = $this->view->render($template, true);

    //            list($header, $body) = preg_split('/(\r\n|\r|\n){2}/', $eml, 2);

    //            $mail = new Mail();
    //            $mail->from(Mail::noreplyAt());
    //            if (preg_match_all('/^([^:]+):\s*([^\r\n]+)/', $header, $match)) {
    //                foreach ($match[1] as $i => $key) {
    //                    $func = strtolower($key);
    //                    if (method_exists($mail, $func)) {
    //                        $mail->$func($match[2][$i]);
    //                    } else {
    //                        $mail->setHeader($key, $match[2][$i]);
    //                    }
    //                }
    //            }
    //            $mail->to();
    //            $mail->to($email);
    //            $mail->message($body);

    //            $mail->send();
    //        }
    //    }

    //    exit;
    //}

    private function sprintf($format, $data): string
    {
        return str_replace(
            ['%s','%t','%n'],
            [$data['subject'],$data['title'],$data['receipt_number']],
            $format
        );
    }

    public function saveSearchOptions()
    {
        if ($this->request->param('submitter') !== 's1_clear') {
            $andor = $this->request->param('andor');
            if ($andor !== 'AND' && $andor !== 'OR') {
                $andor = 'AND';
            }
            $search_options = [
                'issue_date_start' => $this->request->param('issue_date_start'),
                'issue_date_end' => $this->request->param('issue_date_end'),
                'andor' => $andor,
            ];
            $this->session->param(self::SEARCH_OPTIONS_KEY, $search_options);
        } else {
            $this->session->clear(self::SEARCH_OPTIONS_KEY);
        }

        $response = [[$this, 'didSetSearchOptions'], []];
        $this->postReceived('', 0, $response, []);
    }

    public function didSetSearchOptions()
    {
        return ['type' => 'close'];
    }
}
