<?php
namespace Forge\Modules\ForgeTournaments\Calculations;

require_once(FORGE_TOURNAMENTS_LIBS_DIR . 'formula-parser/FormulaParser.php');
use FormulaParser\FormulaParser;

abstract class CalcUtils {

    static function applyFormula($formula, $orig_variable_values, $precision=4) {
        try {
            list($mapping, $new_variable_values) = static::remapVariableNames($orig_variable_values);
            $formula = static::remapFormula($formula, $mapping);
            
            $parser = new FormulaParser($formula, $precision);
            $parser->setValidVariables(array_keys($new_variable_values));
            $parser->setVariables($new_variable_values);

            $result = $parser->getResult();

        } catch (\Exception $e) {
            static::printFormulaError($e, $orig_variable_values);
            throw $e;
        }

        if(is_object($result[1])) {
            static::printFormulaError($result[1], $orig_variable_values);
            throw new \Exception($result[1]);
        }

        return $result[1];
    }

    private static function remapFormula($formula, $remapping) {
        $formula = preg_replace_callback('/[a-zA-Z_.]+/is', function($what) use($remapping) {
            if(isset($remapping[$what[0]])) {
                return $remapping[$what[0]];
            }
            return $what[0];
        }, $formula);

        return $formula;
    }

    /**
     * Ensure the Formula contains only variables of length 1.
     * So for the formula
     * players * time -remap-> x * y
     * players -remap-> a
     * time -remap-> b
     * ect.
     * 
     */
    private static function remapVariableNames($orig_variable_values) {
        static $abc = ['a', 'b', 'c', 'd', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $new_variable_values = [];
        $mapping = [];
        $idx = 0;
        foreach ($orig_variable_values as $key => $value) {
            $new_variable_values[$abc[$idx]] = $value;
            $mapping[$key] = $abc[$idx];
            $idx++;
        }
        return [$mapping, $new_variable_values];
    }

    private static function printFormulaError($error, $variable_values) {
        error_log("Could not calculate formula $formula with following values:");
        error_log(print_r($variable_values, 1));
        error_log($e->getMessage());
    }

}