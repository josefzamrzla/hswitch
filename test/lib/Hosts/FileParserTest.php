<?php
class Hosts_FileParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Hosts_FileParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new Hosts_FileParser();
    }

    public function testIsHostsLine()
    {
        $this->assertFalse($this->parser->isHostsLine("#sgfsdfsdfd1548 121 3"));
        $this->assertTrue($this->parser->isHostsLine("somehost 13.1.4.7"));
        $this->assertTrue($this->parser->isHostsLine("# somehost 13.1.4.7"));
    }

    public function testReplaceMultipleComments()
    {
        $this->assertEquals("", $this->parser->replaceMultipleComments(""));
        $this->assertEquals("string", $this->parser->replaceMultipleComments("string"));
        $this->assertEquals("#string#", $this->parser->replaceMultipleComments("#string#"));
        $this->assertEquals("#string#", $this->parser->replaceMultipleComments("####string##"));
        $this->assertEquals("# string #", $this->parser->replaceMultipleComments("# string #"));
    }

    public function testParseGroupName()
    {
        $this->assertFalse($this->parser->parseGroupName(NULL));
        $this->assertFalse($this->parser->parseGroupName(""));
        $this->assertFalse($this->parser->parseGroupName("invalidgroup"));
        $this->assertFalse($this->parser->parseGroupName("invalidgroup #comment #comment"));

        $this->assertEquals("group", $this->parser->parseGroupName("####    group #comment #comment"));
        $this->assertEquals("group", $this->parser->parseGroupName("  #    group #comment #comment"));
        $this->assertEquals("group", $this->parser->parseGroupName("  #    group some string after"));
    }

    public function testParseHostsLine()
    {
        $this->assertEquals(array(), $this->parser->parseHostsLine(""));
        $this->assertEquals(array("1.2.3.4" => array("host::on")), $this->parser->parseHostsLine("1.2.3.4\thost"));
        $this->assertEquals(array("1.2.3.4" => array("host::off")), $this->parser->parseHostsLine("#1.2.3.4\thost"));
        $this->assertEquals(array("1.2.3.4" => array("host::off")), $this->parser->parseHostsLine("# 1.2.3.4\thost"));
        $this->assertEquals(array("1.2.3.4" => array("host::off")), $this->parser->parseHostsLine("1.2.3.4\t#host"));
        $this->assertEquals(array("1.2.3.4" => array("host::off")), $this->parser->parseHostsLine("1.2.3.4\t# host # "));
        $this->assertEquals(array("unknown" => array("invalid::off", "host::off")), $this->parser->parseHostsLine("invalid host"));
    }
}