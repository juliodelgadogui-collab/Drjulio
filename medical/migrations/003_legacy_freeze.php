<?php
foreach(['atestados','laudos'] as $t){
 ensure_column($t,'pdf_blob','BLOB');ensure_column($t,'emissor_snapshot','TEXT');ensure_column($t,'cid_consentimento','INTEGER DEFAULT 0');
 $fields=array_column(db()->query('PRAGMA table_info('.$t.')')->fetchAll(),'name');$tests=[];
 foreach($fields as $c)if(!in_array($c,['status','cancelado_em','cancelado_motivo','pdf_blob','emissor_snapshot']))$tests[]='NEW.'.$c.' IS NOT OLD.'.$c;
 $tests[]="(OLD.pdf_blob IS NOT NULL AND (NEW.pdf_blob IS NOT OLD.pdf_blob OR NEW.emissor_snapshot IS NOT OLD.emissor_snapshot))";
 $tests[]="NEW.status NOT IN ('EMITIDO','CANCELADO')";$tests[]="(OLD.status='CANCELADO' AND NEW.status<>'CANCELADO')";
 db()->exec('CREATE TRIGGER IF NOT EXISTS freeze_'.$t.' BEFORE UPDATE ON '.$t.' WHEN '.implode(' OR ',$tests)." BEGIN SELECT RAISE(ABORT,'Documento imutavel'); END");
 db()->exec('CREATE TRIGGER IF NOT EXISTS retain_'.$t.' BEFORE DELETE ON '.$t." BEGIN SELECT RAISE(ABORT,'Retencao documental'); END");
}
