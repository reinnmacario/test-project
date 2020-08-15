<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    //
    public function index()
    {

    }
    
    public function parseImport(Request $request)
    {   
        // open csv file
        $path = $request->file('csv_file')->getRealPath();
        $recordArray = array_map('str_getcsv', file($path));
        $fieldsStr = strtolower(implode(',',$recordArray[0]));
        $csvField = explode(',',$fieldsStr); // csv header
        array_shift($recordArray); // csv records
        $recordJsonArray = [];
        // assign header name as key in record array
        for ($i=0; $i < count($recordArray) ; $i++) { 
            for ($j=0; $j < count($csvField); $j++) { 
                $recordJsonArray[$i][$csvField[$j]] =  $recordArray[$i][$j];
            }
        }

        $templates = json_decode($request->templates);
        // loop all templates
        foreach ($templates as $key => $value) {
            // merge subject and message strings 
            $subject = $value->subject;
            $message = $value->message;
            $templateStr = $subject.' '.$message;
            $templateArray = $this->getInterpolatedData($templateStr)[1];
            
            // check if array contains all array values from another array
            $containsSearch = count(array_intersect($templateArray, $csvField)) == count($templateArray);
            // check if constains search has a value or key is the last index in array
            if ($containsSearch || $key == (count($templates) -1) ) {
                $subjectArray = $this->getInterpolatedData($subject)[1];
                $messageArray = $this->getInterpolatedData($message)[1];
                
                // set compiled template array
                $compiledTemplateArray = [];
                $compiledTemplateArray = $this->formatTemplate($compiledTemplateArray, 'subject', $subject, $recordJsonArray);
                $compiledTemplateArray = $this->formatTemplate($compiledTemplateArray, 'message', $message, $recordJsonArray);
                $compiledTemplateArray = $this->removeNoMatchingInterpolatedFields($subject, $message, $compiledTemplateArray);
                break;  // stop the loop for template finding since there is a match
            } 
        }
         return json_encode($compiledTemplateArray);
    }

    public function removeNoMatchingInterpolatedFields($subject, $message, $templateArray) {
        $subjectInterpolated = $this->getInterpolatedData($subject)[0];
        $messageInterpolated = $this->getInterpolatedData($message)[0];
        // replace all interpolated fields that has no match found in the csv headers
        foreach ($templateArray as $key => $compiledValue) {
            foreach ($subjectInterpolated as $value) {
                $templateArray[$key]['subject'] = str_replace($value, '', $compiledValue['subject']);
            }
            foreach ($messageInterpolated as $value) {
                $templateArray[$key]['message']  = str_replace($value, '', $compiledValue['message']);
            }
        }

        return $templateArray;
    }

    public function getInterpolatedData($templateStr) {
        preg_match_all('/{{(.*?)}}/', $templateStr, $templateStrMatches);
        return $templateStrMatches;
    }

    public function formatTemplate($templateArray, $templateKey, $templateField,  $recordJsonArray) {
        // loop each rows in csv
        for ($a=0; $a < count($recordJsonArray) ; $a++) { 
            $newTemplateField = $templateField;
            // loop all headers that were interpolated
            foreach ($recordJsonArray[$a] as $key => $recordJsonArrayIndex) {
                $newTemplateField = str_replace("{{".$key."}}",
                $recordJsonArrayIndex, 
                $newTemplateField);  
            }
          
            $templateArray[$a][$templateKey] = $newTemplateField;
        }

        return $templateArray; 
    }
}
