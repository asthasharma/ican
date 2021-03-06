<?php 
require 'connection.php';
include ('header.php');

// if(isset($_SESSION['email'])){

    $q_id=$_GET['id'];
    $final_results=[];
    try{
        $conn = new PDO("mysql:host=$servername;dbname=ican", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql="SELECT q.quiz_type_id as qt_id, q.id as q_id, q.question as question, c.quiz_question_id as qc_id, c.choice_id as choice_id, c.choice as choice, c.is_right_choice as right_choice
                FROM quiz_choices as c  
                LEFT outer JOIN quiz_questions as q ON c.quiz_question_id=q.id
                where q.quiz_type_id='$q_id'";
        $result = $conn->prepare($sql);
        $result->execute();
        $results = $result->fetchAll(PDO::FETCH_ASSOC);

        $final=[];
        $x=0;
        $question_array=[];
        $y=0;
        foreach ($results as $key => $value) {
            
            if(!in_array($value['question'],$question_array))
            {
            $final[$x]['question']['qt_id'] = $value['qt_id'];
            $final[$x]['question']['q_id'] = $value['q_id'];
            $final[$x]['question']['question'] = $value['question'];
              
            }
            array_push($question_array,$value['question']);

            $final[$x]['choice']['qc_id'][]=$value['qc_id'];
            $final[$x]['choice']['right_choice'][]=$value['right_choice'];
            $final[$x]['choice']['choices'][]=$value['choice'];
            $y++;

            if($y%4==0){
                $x++;
            }
        }

        // echo "<pre>";
        // print_r($final);
        // echo "</pre>";

        echo '<div class="row top-margin-page" id="start_quiz_div">';
        echo '<h1 class="center-block start_q_h1"> Let\'s start the test...!!</h1>';
        echo '</div>';
        echo '<div class="container margin-bottom">';
        $no=1;
        $x=[];
        $no_of_questions=[];
        foreach ($final as $key => $val) {      
            $id= $val['question']['q_id'];
            array_push($no_of_questions,$id);
            echo '<ul id='. $id .' class="question-ul">';
            foreach ($val['question'] as $key1 => $value1) {               
                if($key1=='question'){
                echo $no. '.     '.$value1;
                $no++;
                }           
            }

            $right_choice=[];
            foreach ($val['choice']['right_choice'] as $key => $value) {
                $right_choice[]=$value;
            }

            echo '<form action="">'; 
            $count=0;
            foreach ($val['choice']['choices'] as $key2 => $v2) {
                echo '<li class="choice-li"><input class="option-input radio" type="radio" name="'. $id .'"  value="' . $right_choice[$count] .  '"><label>'. $v2 . '</label></li>';                 
                $count++;
            }    
            echo '</form>';            
            echo "</ul>";
        }
        echo '<button class="button_default" type="submit" name="submit-quiz" id="submit-quiz" onclick="getScore()"> Finish </button>';
        echo '</div>';

        echo "<div id='result_table' class='container margin-bottom' ></div>";
    }
// }

    catch(PDOException $e){
    $success=0;
    $message= "Connection failed: " . $e->getMessage();
    }


include ('footer.php');
?>

<script>
    var no_of_questions=[];
    no_of_questions = <?php echo json_encode($no_of_questions); ?>;
    console.log(no_of_questions);

  function getScore() {
 
    var res=[];
    var marked=0;
    var result_array=[];
    var result_analysis=[];
    var score=0;
    var ans='';
    for(x=0; x<no_of_questions.length; x++){
        var marked= $('input[type="radio"][name="'+ no_of_questions[x] +'"]:checked').val();
        console.log("x="+x);
       
        if(marked==null){
            ans ="Not answered";
            result_analysis.push({q_id:(x+1),analysis:ans});   
        }
        if(marked==1) {
            score +=1;
            ans="Correct answer";
            result_analysis.push({q_id:(x+1),analysis:ans});   
        }
        if(marked==0) {
            ans="Wrong answer";
            result_analysis.push({q_id:(x+1),analysis:ans});   
        }
    }

    console.log("score=" , score);
    console.log("result_array=" ,result_array)
    console.log("result_analysis=", result_analysis)

    var heading ="<h3 class='dark-blue'> Here is your quiz score and it's analysis:</h3>"
    var score ='<p class="dark-blue">Score is ' +score+ '/'+ no_of_questions.length + '</p>';
    var table='<table class="dark-blue" id="result_analysis_table">';
    table +="<thead><th>Question No. </th><th> Result</th></thead>";

    result_analysis.forEach(function (element)  {
        table +="<tr>";
        table +="<td>" + element['q_id'] + "</td>";
        table +="<td>" + element['analysis'] + "</td>";
        table +="</tr>"
    });

    $("#result_table").append(heading);
    $("#result_table").append(score);   
    $("#result_table").append(table);
    
    return;
}  


</script>