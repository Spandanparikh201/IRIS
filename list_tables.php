<?php
$c = new mysqli('localhost', 'root', '', 'studentdb');
if ($c->connect_error) { die($c->connect_error); }
$r = $c->query('SHOW TABLES');
while ($row = $r->fetch_array()) { echo $row[0] . "\n"; }
$c->close();
