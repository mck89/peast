<?php
namespace Peast\test;

class PeastTest extends TestBase
{
    public function testES2015()
    {
        $this->assertTrue(\Peast\Peast::ES2015("") instanceof \Peast\Syntax\ES2015\Parser);
        $this->assertTrue(\Peast\Peast::ES6("") instanceof \Peast\Syntax\ES2015\Parser);
    }
    
    public function testES2016()
    {
        $this->assertTrue(\Peast\Peast::ES2016("") instanceof \Peast\Syntax\ES2016\Parser);
        $this->assertTrue(\Peast\Peast::ES7("") instanceof \Peast\Syntax\ES2016\Parser);
    }
    
    public function testES2017()
    {
        $this->assertTrue(\Peast\Peast::ES2017("") instanceof \Peast\Syntax\ES2017\Parser);
        $this->assertTrue(\Peast\Peast::ES8("") instanceof \Peast\Syntax\ES2017\Parser);
    }
    
    public function testES2018()
    {
        $this->assertTrue(\Peast\Peast::ES2018("") instanceof \Peast\Syntax\ES2018\Parser);
        $this->assertTrue(\Peast\Peast::ES9("") instanceof \Peast\Syntax\ES2018\Parser);
    }
    
    public function testES2019()
    {
        $this->assertTrue(\Peast\Peast::ES2019("") instanceof \Peast\Syntax\ES2019\Parser);
        $this->assertTrue(\Peast\Peast::ES10("") instanceof \Peast\Syntax\ES2019\Parser);
    }
    
    public function testLatest()
    {
        $this->assertTrue(\Peast\Peast::latest("") instanceof \Peast\Syntax\ES2019\Parser);
    }
    
    /**
     * @expectedException \Exception
     */
    public function testInvalidVersion()
    {
        \Peast\Peast::ES("");
    }
}