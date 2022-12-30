<?php
namespace Mybizna\Assets\Controllers;

class MybiznaController
{
    public function __invoke()
    {
        $context = [];
        return view('mybizna::index', compact('context'));
    }
}
