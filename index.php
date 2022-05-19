<?php
session_start();

$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$WON = false;
$maxLetters = strlen($guess) - 1;

// Các bộ phận của người cây
$bodyParts = ["nohead","head","throat","body","hand","hands","leg","legs"];

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

// Trả về biến mảng seesion part khi bộ phận chưa tồn tại hoặc mảng bordyParts.
function getParts(){
    global $bodyParts;
    return isset($_SESSION["parts"]) ? $_SESSION["parts"] : $bodyParts;
}

// Thêm bộ phận vào biến mảng session part
function addPart(){
    $parts = getParts();
    array_shift($parts);//Loại bỏ phần tử đầu tiên của mảng bodyParts
    $_SESSION["parts"] = $parts;
}

// Lấy ra hình ảnh hiện tại

function getCurrentPicture($part){
    return "./images/hangman_". $part. ".png";
}

// Lấy bộ phận hiện tại của người cây
function getCurrentPart(){
    $parts = getParts();
    return $parts[0];
}

// trả về đáp án hiện tại lưu trong mảng session word
function getCurrentWord(){
    global $words;
    if(!isset($_SESSION["word"]) && empty($_SESSION["word"])){
        $key = array_rand($words);//Lấy ra 1 phần tử bất kỳ của mảng đáp án
        $_SESSION["word"] = $words[$key];
    }
    return $_SESSION["word"];
}



// Trả về mảng rỗng hoặc từ của người chơi chọn
function getCurrentResponses(){
    return isset($_SESSION["responses"]) ? $_SESSION["responses"] : [];
}

//Thêm từ vào mảng đáp án
function addResponse($letter){
    $responses = getCurrentResponses();
    array_push($responses, $letter);//Thêm 1 phần tử vào cuối mảng
    $_SESSION["responses"] = $responses;
}

// Kiểm tra tính hợp lệ của chữ cái
function isLetterCorrect($letter){
    $word = getCurrentWord();
    $max = strlen($word) - 1;
    for($i=0; $i<= $max; $i++){
        if($letter == $word[$i]){//Kiểm tra tham số truyền vào có trong đáp án hiện tại?
            return true;
        }
    }
    return false;
}

// Kiểm tra từ hợp lệ

function isWordCorrect(){
    $guess = getCurrentWord();
    $responses = getCurrentResponses();
    $max = strlen($guess) - 1;
    for($i=0; $i<= $max; $i++){
        if(!in_array($guess[$i],  $responses)){//Kiểm tra biến session không có trong mảng đáp án.
            return false;
        }
    }
    return true;
}

// Trả về true khi số lượng phần tử trong mảng 

function isBodyComplete(){
    $parts = getParts();
    if(count($parts) <= 1){//Số phần tử trong mảng có nhỏ hơn hoặc bằng 1.
        return true;
    }
    return false;
}


function gameComplete(){
    return isset($_SESSION["gamecomplete"]) ? $_SESSION["gamecomplete"] :false;
}


function markGameAsComplete(){
    $_SESSION["gamecomplete"] = true;
}
 
function markGameAsNew(){
    $_SESSION["gamecomplete"] = false;
}



/* Kiểm tra nút tiếp tục chơi được nhấn*/
if(isset($_GET['start'])){
    restartGame();
}



if(isset($_GET['kp'])){
    $currentPressedKey = isset($_GET['kp']) ? $_GET['kp'] : null;
    // Từ nhấn vào hợp lệ
    if($currentPressedKey 
    && isLetterCorrect($currentPressedKey)
    && !isBodyComplete()
    && !gameComplete()){
        
        addResponse($currentPressedKey);
        if(isWordCorrect()){
            $WON = true;
            markGameAsComplete();
        }
    }else{
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
