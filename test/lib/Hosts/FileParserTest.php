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

    public function testParseHostsLineWithMultipleActiveHosts()
    {
        $expected = array(
            "127.0.0.1" => array("host1::on", "host2::on", "host3::on", "host4::on"));

        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1 host1 host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1\thost1 host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1\thost1\thost2 host3 host4"));
    }

    public function testParseHostsLineWithMultiplePasiveHosts()
    {
        $expected = array(
            "127.0.0.1" => array("host1::off", "host2::off", "host3::off", "host4::off"));

        $this->assertEquals($expected, $this->parser->parseHostsLine("#127.0.0.1 host1 host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("#127.0.0.1 #host1 host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine(" # 127.0.0.1 #host1 host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("# 127.0.0.1 #host1 #host2 #host3 #host4"));
    }

    public function testParseHostsLineWithMixedHosts()
    {
        $expected = array(
            "127.0.0.1" => array("host1::on", "host2::off", "host3::off", "host4::off"));

        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1 host1 #host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1 host1 ####host2 host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1 host1 #host2 #host3 host4"));
        $this->assertEquals($expected, $this->parser->parseHostsLine("127.0.0.1 host1#host2 #host3 #host4"));
    }

    public function testParseEmptyFile()
    {
        $this->assertEquals(array(), $this->parser->parse(""));
    }

    public function testParseWithOneGroup()
    {
        $expected = array(
            "localhost" => array(
                "127.0.0.1" => array("host1::on", "host2::on")
            )
        );

        $file = "#localhost\n
            127.0.0.1\thost1\n
            127.0.0.1 host2\n
            ";

        $this->assertEquals($expected, $this->parser->parse($file));

        $expected = array(
            "localhost" => array(
                "127.0.0.1" => array("host1::on", "host2::on")
            )
        );

        $file = "#localhost\n
            127.0.0.1\thost1 host2\n";

        $this->assertEquals($expected, $this->parser->parse($file));

        $expected = array(
            "localhost" => array(
                "127.0.0.1" => array("host1::on", "host2::on", "host3::off")
            )
        );

        $file = "#localhost\n
            127.0.0.1\thost1 host2#### host3\n";

        $this->assertEquals($expected, $this->parser->parse($file));

        $expected = array(
            "localhost" => array(
                "127.0.0.1" => array("host1::off", "host2::off")
            )
        );

        $file = "#localhost\n
            #127.0.0.1\thost1\n
            # 127.0.0.1 #host2\n
            ";

        $this->assertEquals($expected, $this->parser->parse($file));
    }

    public function testParseWithDefaultGroup()
    {
        $expected = array(
            "__default" => array(
                "1.2.3.4" => array("host1::on", "host2::on"),
                "2.2.2.2" => array("host3::off")
            ),
            "localhost" => array(
                "127.0.0.1" => array("host4::on")
            )
        );

        $file = "
            1.2.3.4 host1 host2\n
            #2.2.2.2 host3\n
            #################\n
            ### localhost ###\n
            #################\n
            127.0.0.1\thost4";

        $this->assertEquals($expected, $this->parser->parse($file));
    }

    public function testParseWithTwoGroups()
    {
        $expected = array(
            "localhost" => array(
                "127.0.0.1" => array("host1::on", "host2::on", "host3::off")
            ),
            "DEVEL" => array(
                "1.2.3.4" => array("host4::on"),
                "2.3.4.5" => array("host5::off"),
                "3.4.5.6" => array("host6::on", "host7::off")
            )
        );

        $file = "
            #localhost\n
            127.0.0.1 host1 host2\n
            #127.0.0.1 host3\n
            \n
            # DEVEL ###\n
            1.2.3.4 host4\n
            2.3.4.5 #host5\n
            3.4.5.6 host6 #host7\n";

        $this->assertEquals($expected, $this->parser->parse($file));
    }
}