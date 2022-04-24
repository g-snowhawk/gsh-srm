<?php
/**
 * This file is part of G.Snowhawk Application.
 *
 * Copyright (c)2016-2017 PlusFive (https://www.plus-5.com)
 *
 * This software is released under the MIT License.
 * https://www.plus-5.com/licenses/mit-license
 */

namespace Gsnowhawk\Srm\Lang;

/**
 * Japanese Languages for Gsnowhawk.
 *
 * @license https://www.plus-5.com/licenses/mit-license  MIT License
 * @author  Taka Goto <www.plus-5.com>
 */
class Ja extends \Gsnowhawk\Common\Lang
{
    const APP_NAME = 'SRM';
    const ALT_NAME = '帳票管理';

    protected $APPLICATION_NAME = self::APP_NAME;
    protected $APPLICATION_LABEL = '帳票発行';
    protected $APP_DETAIL    = self::ALT_NAME.'機能を提供します。';
    protected $SUCCESS_SETUP = self::ALT_NAME.'機能の追加に成功しました。';
    protected $FAILED_SETUP  = self::ALT_NAME.'機能の追加に失敗しました。';

    const SUCCESS_UNAVAILABLE = "帳票を無効にしました";
    const SUCCESS_AVAILABLE   = "帳票を有効にしました";
    const FAILED_UNAVAILABLE  = "帳票を無効にできませんでした";
    const FAILED_AVAILABLE    = "帳票を有効にできませんでした";

    const UPDATED_BILLING_DATE = "締日を更新しました";
    const INCORRECT_DATE_VALUE = "不正な日付です";
    const BILLING_DATE_IS_HOLIDAY = "指定した日付は休日です。このまま更新しますか？";

    const AUTOISSUE_BILL_SUBJECT = "Y年n月ご請求分";
}
