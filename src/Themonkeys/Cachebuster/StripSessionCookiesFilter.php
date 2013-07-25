<?php namespace Themonkeys\Cachebuster;

use Illuminate\Support\Facades\Request;

class StripSessionCookiesFilter {
    private $patterns = array();

    public function addPattern($pattern) {
        $this->patterns []= $pattern;
    }

    public function filter($request, $response = null) {
        $url = Request::path();
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                header_remove('Set-Cookie');
            }
        }
    }
}