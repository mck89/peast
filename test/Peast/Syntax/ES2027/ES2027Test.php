<?php
namespace Peast\test\Syntax\ES2027;

class ES2027Test extends \Peast\test\Syntax\ES2026\ES2026Test
{
    protected $parser = "ES2027";

    protected function getTestVersions()
    {
        return array(
            "ES2015", "ES2016", "ES2017", "ES2018", "ES2019",
            "ES2020", "ES2021", "ES2022", "ES2023", "ES2024",
            "ES2025", "ES2026", "ES2027"
        );
    }
}