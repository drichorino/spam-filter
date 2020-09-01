<?php

$positiveTotal = 0;
$negativeTotal = 0;
$pA = 0;
$pNotA = 0;
$trainPositiveWord = array();
$trainNegativeWord = array();

//$email = 'Go until jurong point, crazy.. Available only in bugis n great world la e buffet... Cine there got amore wat...';
//$email = 'WINNER!! As you receive a prize reward! To claim call 09061701461. Claim code KL341. Valid 12 hours only.';
$email = 'Hello! Are you the winner?';

echo PHP_EOL;
train();
$result = classify($email);
echo PHP_EOL;
if($result){
    echo "EMAIL IS SPAM!";
}
else{
    echo "EMAIL IS NOT SPAM!";
}
echo PHP_EOL;

//runs once on training data
function train(){
    global $pA; //number of spam emails over total emails
    global $pNotA; //number of NOT spam emails over total emails
    $total = 0;
    $numSpam = 0;
    $body='';
    $label='';
    
    //$handle = fopen($_FILES['csvfile']['tmp_name'], "r");
    
    $handle = fopen("spam.csv", "r");
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        if($data[0]!=''){
            //echo $data[0];
            if($data[1]=='spam'){
                $numSpam=$numSpam+1;
            }
            $total=$total+1;
            $body=$data[0];
            $label=$data[1];
            $body=strtolower($body);
            $body = preg_replace('/[^\w\s]/', '', $body);
            processEmail($body,$label);
        }       
    }
    
    $pA = $numSpam/$total;
    $pNotA = ($total-$numSpam)/$total;
    
    //echo $total. PHP_EOL;
    //echo $numSpam. PHP_EOL;
    //echo $pA. PHP_EOL;
    //echo $pNotA. PHP_EOL;        
}

//counts the words in a specific email
function processEmail($body,$label){
    global $negativeTotal;
    global $positiveTotal;
    $tok = strtok($body, " ");
    while ($tok !== false){
        //echo "Word=$tok ";
        if($label=='spam'){
            //$trainPositiveWord[] = $tok;            
            trainPositiveWord($tok);
            $positiveTotal = $positiveTotal + 1;
        } else {
            trainNegativeWord($tok);
            $negativeTotal = $negativeTotal + 1;
        }
        $tok = strtok(" ");
    }
}

//gives the conditional probability of a word
function conditionalWord($word, $spam){
    global $positiveTotal;
    global $negativeTotal;
    global $trainPositiveWord;
    global $trainNegativeWord;

    if($spam){
        if(array_key_exists($word,$trainPositiveWord)){
            return $trainPositiveWord[$word]/$positiveTotal;
        }  
    }
    else{
        if(array_key_exists($word,$trainNegativeWord)){
            return $trainNegativeWord[$word]/$negativeTotal;
        }       
    }

}

//calculates the probabilty if a given word on an email is spam
function conditionalEmail($email,$spam){
    $result = 1.0;
    $word = strtok($email, " ");
    while ($word !== false){
        echo "WORD: ".$word .PHP_EOL;
        echo "CONDITIONAL WORD SCORE: ".conditionalWord($word,$spam) .PHP_EOL;
        
        $conditionalWord = conditionalWord($word,$spam);  
        $result *= $conditionalWord;  
        echo "RESULT: ".$result.PHP_EOL;  
        $word = strtok(" ");
    }
    return $result;
}

//classifies if a given email is a spam or not
function classify($email){
    $email = strtolower($email);
    $email = preg_replace('/[^\w\s]/', '', $email);
    global $pA;
    global $pNotA;
    $isSpam = $pA * conditionalEmail($email, TRUE);
    echo PHP_EOL;
    echo "=====================================".PHP_EOL;
    echo PHP_EOL;
    $notSpam = $pNotA * conditionalEmail($email, FALSE);
    echo PHP_EOL;
    echo "isSpam Score: ".$isSpam .PHP_EOL;
    echo "notSpam Score: ".$notSpam .PHP_EOL;
    return $isSpam > $notSpam;    
}

function trainPositiveWord($word){
    global $trainPositiveWord;

    if(array_key_exists($word,$trainPositiveWord)){
        foreach($trainPositiveWord as $k => $val){
            if($k == $word){
                    $trainPositiveWord[$k] += 1;
                }
        }
    } else {
        $trainPositiveWord[$word] = 1;
    }   
}

function trainNegativeWord($word){
    global $trainNegativeWord;
    
    if(array_key_exists($word,$trainNegativeWord)){
        foreach($trainNegativeWord as $k => $val){
            if($k == $word){
                    $trainNegativeWord[$k] += 1;
                }
        }
    } else {
        $trainNegativeWord[$word] = 1;
    }   
}

?>