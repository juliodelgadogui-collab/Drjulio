<?php
function run_migrations() {
 $d=db(); $d->exec('CREATE TABLE IF NOT EXISTS schema_migrations(version INTEGER PRIMARY KEY, applied_at TEXT NOT NULL)');
 foreach ([1=>'001_legacy.php',2=>'002_integrated.php',3=>'003_legacy_freeze.php',4=>'004_cid_official.php',5=>'005_multi_tenant.php'] as $version=>$file) {
  $s=$d->prepare('SELECT 1 FROM schema_migrations WHERE version=?');$s->execute([$version]);if($s->fetchColumn())continue;
  $d->exec('BEGIN IMMEDIATE');
  try {
   $s->execute([$version]);
   if(!$s->fetchColumn()) { require __DIR__.'/'.$file; $d->prepare('INSERT INTO schema_migrations VALUES(?,?)')->execute([$version,date('c')]); }
   $d->exec('COMMIT');
  } catch(Throwable $e) { $d->exec('ROLLBACK');throw $e; }
 }
}
