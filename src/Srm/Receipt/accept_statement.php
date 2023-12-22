<?php

$statement = <<<SQL
SELECT r.issue_date,r.receipt_number,r.subject,r.draft,
       r.due_date,r.receipt,r.billing_date,r.unavailable,
       r.userkey,r.templatekey,
       MD5(CONCAT(r.issue_date,r.receipt_number,r.userkey,r.templatekey,r.draft)) AS `hash`,
       c.company
  FROM table::receipt AS r
  JOIN table::receipt_to AS c
    ON r.client_id = c.id
 WHERE shared IN ({$shared}) AND unavailable <> '1' AND draft <> '1'{$between}
 ORDER BY r.issue_date DESC,r.receipt_number DESC
SQL;
