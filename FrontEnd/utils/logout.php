<?php
session_start();
session_unset();
session_destroy();

// Logout ပြီးရင် shop folder ထဲက index.php ဆီ ပြန်ပို့မယ်
if(isset($_GET['supplier_id'])) {
    $sid = $_GET['supplier_id'];
    // utils ထဲကနေ shop ထဲကို ပြန်သွားဖို့ ../shop/ ကို သုံးရပါမယ်
    header("Location: ../shop/index.php?supplier_id=$sid");
} else {
    header("Location: ../shop/index.php"); 
}
exit();
?>