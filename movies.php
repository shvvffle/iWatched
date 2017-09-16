<?php
    require_once("config.php");

    if(isset($_SESSION["user_id"])){
        $user_logged = $_SESSION["user_id"];
        // get user data
        $query = $db->prepare("
                    SELECT user_id, username FROM users WHERE user_id = ?
                ");
        $query->execute(array($user_logged));
        $user = $query->fetchAll( PDO::FETCH_ASSOC );

        // load movies
        $fetch_movie = $db->prepare("
                SELECT * FROM movies WHERE user_id = ? ORDER BY movie_id
            ");

        $fetch_movie->execute(array($user_logged));
        $movies = $fetch_movie->fetchAll( PDO::FETCH_ASSOC );
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>iWatched - Movies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="images/favicon-16x16.png" sizes="16x16" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <script>
        window.onload = function() {
            var buttons = document.querySelectorAll('.delete-movie');

            for(var i = 0; i < buttons.length; i++) {

                buttons[i].onclick = function() {
                    var movie_id = this.dataset.id;
                    var param = "request=delete&movie_id=" + movie_id;

                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "requests.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.send(param);

                    this.parentNode.parentNode.parentNode.parentNode.remove();
                    var success_msg = document.getElementById('success_msg').innerHTML = "The movie has been deleted successfully";
                }
            }
        }
    </script>   
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <h1><a href="index.php" class="logo">iWatched</a></h1>
            </div>
            <div class="search-bar">
                <div class="search-container">
                  <div class="search-icon-btn">
                    <i class="fa fa-search"></i>
                  </div>
                  <div class="search-input">
                    <input type="search" class="search-bar" placeholder="Search for a movie...">
                  </div>
                </div>
            </div>
            <div class="menu">
                <a href="#">Movies</a>
                <p class='welcome'>Welcome back <?php echo $user[0]["username"]; ?>!</p>
            </div>
        </nav>
    </header>
    <section class="intro-movie-list">
    <?php
        if(empty($movies)){ ?>
            <h2>Hm... you haven't watched any movie yet, <span class="red"><?php echo $user[0]["username"]; ?>?</span> I find it hard to believe.</h2>
    <?php } else { ?>
            <h2>Don't you have anything better to do, <span class="red"><?php echo $user[0]["username"]; ?>?</span> Life isn't only about watching movies...</h2>
    <?php } ?>
        <div class="movie-list">
        <?php
            foreach($movies as $movie) {
        ?>
            <div class="container">
              <div class="card-media">
                <!-- media container -->
                <div class="card-media-object-container">
                  <div class="card-media-object" style="background-image: url(images/<?php echo $movie["cover"];?>);"></div>
                </div>
                <!-- body container -->
                <div class="card-media-body">
                  <div class="card-media-body-top">
                    <span><?php echo $movie["release_year"];?></span>
                    <div class="card-movie-actions">
                        <a href="movie_detail.php?movie_id=<?php echo $movie["movie_id"];?>">
                            <span class="fa fa-search-plus" aria-hidden="true"></span>
                        </a>
                        <a class="delete-movie" data-id="<?php echo $movie["movie_id"];?>">
                            <span class="fa fa-trash-o red" aria-hidden="true"></span>
                        </a>
                    </div>
                  </div>
                  <span class="card-media-body-heading red"><?php echo $movie["title"];?></span>
                  <div class="card-media-body-supporting-bottom">
                    <span class="card-media-body-supporting-bottom-text"><?php echo $movie["genre"];?></span>
                    <span class="card-media-body-supporting-bottom-text float-right"><?php echo $movie["director"];?></span>
                  </div>
                </div>
              </div>
            </div>
        <?php
            }
        ?>
        <div id="success_msg" class="red center"></div>
        </div>
        <div class="movies-actions">
            <button><a href="add_movie.php">Add a Movie</a></button>
        </div>
    </section>
</body>
</html>