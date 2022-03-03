<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="skin/screen.css">
    <link rel="icon" href="favicon.ico">
    <style>
       <?php  echo $this->style;     ?>
    </style>
  </head>
 
  <body>
     <header class = "page-header">
         <div><img src="skin/images/head.jpg" alt="" style="width: 200px;"></div>
         <nav>
           <ul>
             <?php
              foreach ($this->makeMenu() as $text => $link) {
                echo "<li><a href = '$link'>$text</a></li>";
              }
              ?>
           </ul>
         </nav>
     </header>

     <main class="page-main">
        <?php if($feedback !== ""){ ?>
             <h1 class="feedback"><?php echo $feedback; ?></h1>
        <?php } ?>
        <?php echo "<h1 class='titre'>$title<h1>"; ?>
        <?php echo "<div class ='content'>$content<div>"; ?>
     </main>

     <footer class = "page-footer">


     </footer>
  </body>
</html>
