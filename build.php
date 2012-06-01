<?php
unlink(__DIR__ . "/src/autoload.php");
system("phpab -o " . __DIR__ . "/src/autoload.php -b " . __DIR__ . "/src " . __DIR__);
/*
$p = new Phar("build/script.phar");
$p->buildFromDirectory('src/');
$p->setStub("<?php
__HALT_COMPILER();");

//$p->compressFiles(Phar::GZ);
if (is_resource($fp = fopen(__DIR__ . "/build/run", "w"))) {

    fwrite($fp, "<?php\nrequire_once \"phar://script.phar/autoload.php\";\n\$test = new Hswitch\\Test();\n");
    fwrite($fp, "echo \"\\n\" . \$test->bar() . \"\\n\";");
    fclose($fp);
} else {
    die("Cannot fopen /build/run for writing");
}
*/