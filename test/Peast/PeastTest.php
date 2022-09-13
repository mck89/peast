<?php
namespace Peast\test;

class PeastTest extends TestBase
{
    public function testES2015()
    {
        $this->assertTrue(\Peast\Peast::ES2015("")->getFeatures() instanceof \Peast\Syntax\ES2015\Features);
        $this->assertTrue(\Peast\Peast::ES6("")->getFeatures() instanceof \Peast\Syntax\ES2015\Features);
    }
    
    public function testES2016()
    {
        $this->assertTrue(\Peast\Peast::ES2016("")->getFeatures() instanceof \Peast\Syntax\ES2016\Features);
        $this->assertTrue(\Peast\Peast::ES7("")->getFeatures() instanceof \Peast\Syntax\ES2016\Features);
    }
    
    public function testES2017()
    {
        $this->assertTrue(\Peast\Peast::ES2017("")->getFeatures() instanceof \Peast\Syntax\ES2017\Features);
        $this->assertTrue(\Peast\Peast::ES8("")->getFeatures() instanceof \Peast\Syntax\ES2017\Features);
    }
    
    public function testES2018()
    {
        $this->assertTrue(\Peast\Peast::ES2018("")->getFeatures() instanceof \Peast\Syntax\ES2018\Features);
        $this->assertTrue(\Peast\Peast::ES9("")->getFeatures() instanceof \Peast\Syntax\ES2018\Features);
    }
    
    public function testES2019()
    {
        $this->assertTrue(\Peast\Peast::ES2019("")->getFeatures() instanceof \Peast\Syntax\ES2019\Features);
        $this->assertTrue(\Peast\Peast::ES10("")->getFeatures() instanceof \Peast\Syntax\ES2019\Features);
    }

    public function testES2020()
    {
        $this->assertTrue(\Peast\Peast::ES2020("")->getFeatures() instanceof \Peast\Syntax\ES2020\Features);
        $this->assertTrue(\Peast\Peast::ES11("")->getFeatures() instanceof \Peast\Syntax\ES2020\Features);
    }

    public function testES2021()
    {
        $this->assertTrue(\Peast\Peast::ES2021("")->getFeatures() instanceof \Peast\Syntax\ES2021\Features);
        $this->assertTrue(\Peast\Peast::ES12("")->getFeatures() instanceof \Peast\Syntax\ES2021\Features);
    }

    public function testES2022()
    {
        $this->assertTrue(\Peast\Peast::ES2022("")->getFeatures() instanceof \Peast\Syntax\ES2022\Features);
        $this->assertTrue(\Peast\Peast::ES13("")->getFeatures() instanceof \Peast\Syntax\ES2022\Features);
    }

    public function testES2023()
    {
        $this->assertTrue(\Peast\Peast::ES2023("")->getFeatures() instanceof \Peast\Syntax\ES2023\Features);
        $this->assertTrue(\Peast\Peast::ES14("")->getFeatures() instanceof \Peast\Syntax\ES2023\Features);
    }
    
    public function testLatest()
    {
        $this->assertTrue(\Peast\Peast::latest("")->getFeatures() instanceof \Peast\Syntax\ES2023\Features);
    }

    public function testInvalidVersion()
    {
        $this->expectException('Exception');

        \Peast\Peast::ES("");
    }
}