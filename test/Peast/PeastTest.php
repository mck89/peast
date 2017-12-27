<?php
namespace test\Peast;

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
    
    public function testLatest()
    {
        $this->assertTrue(\Peast\Peast::latest("") instanceof \Peast\Syntax\ES2017\Parser);
    }
    
    /**
     * @expectedException \Exception
     */
    public function testInvalidVersion()
    {
        \Peast\Peast::ES("");
    }
}