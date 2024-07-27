<?php

class RAdiantiArrayService{

    static function converterEmTexto(array $array): string{
        $string = '';
		foreach ($array as $key => $value) {
			$string .= $key . ': ' . $value . '<br>';
		}
		return $string;
    }

}