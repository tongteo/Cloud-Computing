<?php
session_start();

$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$WON = false;
$maxLetters = strlen($guess) - 1;

// Các bộ phận của người cây
$bodyParts = ["nohead","head","body","hand","hands","leg","legs"];

// Các từ khóa ngẫu nhiên
$words = [
	"HAIBATRUNG",
	"LYNAMDE" , 
	"NGOQUYEN",
	"DINHBOLINH", 
	"LEHOAN",
	"LYCONGUAN",
	"LYTHUONGKIET",
	"TRANNHANTONG",
	"TRANHUNGDAO",
	"LELOI",
	"NGUYENTRAI",
	"NGUYENHUE",
	"HOCHIMINH",
	"HUNGVUONG"
];





// Khởi động lại trò chơi
function restartGame(){
    session_destroy();
    session_start();

}

// Kiểm tra 
function getParts(){
    global $bodyParts;
    return isset($_SESSION["parts"]) ? $_SESSION["parts"] : $bodyParts;
}

// Them bo phan vao nguoi cay
function addPart(){
    $parts = getParts();
    array_shift($parts);//Loại bỏ phần tử đầu tiên của mảng
    $_SESSION["parts"] = $parts;
}


function getCurrentPicture($part){
    return "./images/hangman_". $part. ".png";
}

// Lay bo phan hien tai cua nguoi cay
function getCurrentPart(){
    $parts = getParts();
    return $parts[0];
}

// Lay tu hien tai
function getCurrentWord(){
    global $words;
    if(!isset($_SESSION["word"]) && empty($_SESSION["word"])){
        $key = array_rand($words);
        $_SESSION["word"] = $words[$key];
    }
    return $_SESSION["word"];
}



// Lay phan hoi tu nguoi choi
function getCurrentResponses(){
    return isset($_SESSION["responses"]) ? $_SESSION["responses"] : [];
}

//Them phan hoi
function addResponse($letter){
    $responses = getCurrentResponses();
    array_push($responses, $letter);
    $_SESSION["responses"] = $responses;
}

// Kiem tra chu cai hop le
function isLetterCorrect($letter){
    $word = getCurrentWord();
    $max = strlen($word) - 1;
    for($i=0; $i<= $max; $i++){
        if($letter == $word[$i]){
            return true;
        }
    }
    return false;
}

// Kiem tra tu hop le

function isWordCorrect(){
    $guess = getCurrentWord();
    $responses = getCurrentResponses();
    $max = strlen($guess) - 1;
    for($i=0; $i<= $max; $i++){
        if(!in_array($guess[$i],  $responses)){
            return false;
        }
    }
    return true;
}

// Kiem tra nguoi cay du bo phan

function isBodyComplete(){
    $parts = getParts();
    // is the current parts less than or equal to one
    if(count($parts) <= 1){
        return true;
    }
    return false;
}


// Kiem tra game hoan thanh
function gameComplete(){
    return isset($_SESSION["gamecomplete"]) ? $_SESSION["gamecomplete"] :false;
}


// Dat game hoan thanh
function markGameAsComplete(){
    $_SESSION["gamecomplete"] = true;
}

// Bat dau game moi
function markGameAsNew(){
    $_SESSION["gamecomplete"] = false;
}



/* Kiem tra nut khoi dong game duoc nhan*/
if(isset($_GET['start'])){
    restartGame();
}



if(isset($_GET['kp'])){
    $currentPressedKey = isset($_GET['kp']) ? $_GET['kp'] : null;
    // Tu nhan vao hop le
    if($currentPressedKey 
    && isLetterCorrect($currentPressedKey)
    && !isBodyComplete()
    && !gameComplete()){
        
        addResponse($currentPressedKey);
        if(isWordCorrect()){
            $WON = true; // game complete
            markGameAsComplete();
        }
    }else{
        // Treo co nguoi cay :D
        if(!isBodyComplete()){
           addPart(); 
           if(isBodyComplete()){
               markGameAsComplete();
           }
        }else{
            markGameAsComplete();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Game đoán chữ</title>
</head>
    <body style="background: deepskyblue">
        
        <div style="margin: 0 auto; background: #dddddd; width:900px; height:900px; padding:5px; border-radius:3px;">
            
            <!-- Nơi hiển thị ảnh -->
            <div style="display:inline-block; width: 500px; background:#fff;">
                 <img style="width:100%; display:inline-block;" src="<?php echo getCurrentPicture(getCurrentPart());?>"/>
          
               <?Php if(gameComplete()):?>
                    <h1>HOÀN THÀNH TRÒ CHƠI</h1>
                <?php endif;?>
                <?php if($WON  && gameComplete()):?>
                    <p style="color: darkgreen; font-size: 25px;">Chúc mừng bạn đã đoán chính xác!</p>
                <?php elseif(!$WON  && gameComplete()): ?>
                    <p style="color: darkred; font-size: 25px;">Bạn đã không đoán được từ khóa :((</p>
                <?php endif;?>
            </div>
                    <!-- Nơi hiển thị bảng chữ cái -->
            <div style="float:right; display:inline; vertical-align:top;">
                <h1>Game đoán chữ</h1>
                <div style="display:inline-block;">
                    <form method="get">
                    <?php
                        $max = strlen($letters) - 1;
                        for($i=0; $i<= $max; $i++){
                            echo "<button type='submit' name='kp' value='". $letters[$i] . "'>".
                            $letters[$i] . "</button>";
                            if ($i % 7 == 0 && $i>0) {//Hiển thị tối đa 7 chữ cái trên 1 dòng
                               echo '<br><br>';
                            }
                            
                        }
                    ?>
                    <br><br>
                    <!-- Khởi động lại trò chơi -->
                    <button type="submit" name="start">Tiếp tục chơi</button>
                    </form>
                </div>
            </div>
            
            <div style="margin-top:20px; padding:15px; background: lightseagreen; color: #fcf8e3">
                <!-- Hiển thị từ người chơi đoán -->
                <?php 
                 $guess = getCurrentWord();
                 $maxLetters = strlen($guess) - 1;
                for($j=0; $j<= $maxLetters; $j++): $l = getCurrentWord()[$j]; ?>
                    <?php if(in_array($l, getCurrentResponses())):?>
                        <span style="font-size: 35px; border-bottom: 3px solid #000; margin-right: 5px;"><?php echo $l;?></span>
                    <?php else: ?>
                        <span style="font-size: 35px; border-bottom: 3px solid #000; margin-right: 5px;">&nbsp;&nbsp;&nbsp;</span>
                    <?php endif;?>
                <?php endfor;?>
				 <h2>Gợi ý: Tên 14 vị danh nhân, anh hùng dân tộc Việt Nam.</h2>
            </div>
            
        </div>
        
        
        
    </body>
    
    
</html>
