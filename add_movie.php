<?php
    require_once("config.php");

    if(isset($_SESSION["user_id"])){
        $user_logged = $_SESSION["user_id"];
    	// get user data

	 	$user_query = $db->prepare("
	 		SELECT user_id, username FROM users WHERE user_id = ?
	    ");
	    $user_query->execute( array($user_logged) );
	    $user = $user_query->fetchAll( PDO::FETCH_ASSOC );


	    // check form validation
		$allowed_extensions = array(
			"image/jpeg" => ".jpg",
			"image/png" => ".png"
		);

		if($_SERVER['REQUEST_METHOD'] == "POST") {
			foreach($_POST as $key => $value) {
				$_POST[$key] = strip_tags(trim($value));
			}

			if(
				!empty($_POST["title"]) &&
				!empty(is_int($_POST["release_year"])) &&
				!empty($_POST["director"]) &&
				!empty($_POST["actors"]) &&
				!empty($_POST["genre"]) &&
				!empty($_POST["description"]) &&
				!empty($_POST["rating"]) &&
				strlen($_POST["description"]) <= 65535 &&
				strlen($_POST["title"]) <= 255 &&
				strlen($_POST["director"]) <= 255 &&
				strlen($_POST["actors"]) <= 255 &&
				strlen($_POST["genre"]) <= 255 &&
				($_FILES["cover"]["type"] === "image/jpeg" || $_FILES["cover"]["type"] === "image/png") &&
				$_FILES["cover"]["size"] > 0 &&
				$_FILES["cover"]["size"] <= 2000000 &&
				$_FILES["cover"]["error"] === 0
			) {

				/* check and confirm if the movie already exists */
				$query = $db->prepare("SELECT title FROM movies WHERE title = ?");
				$query->execute( array($_POST["title"]) );
				$result = $query->fetchAll( PDO::FETCH_ASSOC );

				if(empty($result)) {
					/* if the movie doesn't exist, INSERT in db */
					$filename = date("YmdHis") . "_" .mt_rand(10000, 99999) . $allowed_extensions[$_FILES["cover"]["type"]];
					move_uploaded_file($_FILES["cover"]["tmp_name"], "images/" . $filename);

					$query = $db->prepare("
						INSERT INTO movies
						(title, release_year, director, actors, genre, description, rating, cover, user_id)
						VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)
					");
					$result = $query->execute(
						array(
							$_POST["title"],
							$_POST["release_year"],
							$_POST["director"],
							$_POST["actors"],
							$_POST["genre"],
							$_POST["description"],
							$_POST["rating"],
							$filename,
							$user_logged
						)
					);

					$movie_id = $db->lastInsertId();

					$message = "Movie added successfully!";
				} else {
					$message = "This movie already exists.";
				}
			} else {
				$message = "Fill in all fields correctly.";
			}
		}
	} else {
		header("Location: index.php");
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <title>iWatched - Add a movie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="images/favicon-16x16.png" sizes="16x16" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <h1><a href="index.php" class="logo">iWatched</a></h1>
            </div>
            <div class="menu">
                <a href="movies.php">Movies</a>
                <p class="welcome">Welcome back <?php echo $user[0]["username"]; ?>!</p>
                <a href="logout.php">Log out</a>
            </div>
        </nav>
    </header>
        <section class="add-movie-form">
	    <?php
			if(isset($message)) echo "<p class='red'>" .$message. "</p>";
		?>
    	<h2>Add the latest movie you've watched</h2>
		<form method="post" action="add_movie.php" enctype="multipart/form-data">
			<div class="title">
				<label>
					<span class="fa fa-film" aria-hidden="true"></span>
					<span class="hidden">Movie Title</span>
				</label>
				<input type="text" name="title" placeholder="Movie Title *" maxlength="255" required>
			</div>
			<div class="release-date">
				<label>
					<span class="fa fa-calendar-o" aria-hidden="true"></span>
					<span class="hidden">Movie Release Year Date</span>
				</label>
				<input type="text" name="release_year" placeholder="Movie Release Year Date *" maxlength="4" required>
			</div>
			<div class="director">
				<label>
					<span class="fa fa-video-camera" aria-hidden="true"></span>
					<span class="hidden">Movie Director</span>
				</label>
				<input type="text" name="director" placeholder="Movie Director *" maxlength="255" required>
			</div>
			<div class="actors">
				<label>
					<span class="fa fa-user-o" aria-hidden="true"></span>
					<span class="hidden">Movie Actors</span>
				</label>
				<input type="text" name="actors" placeholder="Movie Actors *"  maxlength="255" required>
			</div>
			<div class="genre">
				<label>
					<span class="fa fa-flask" aria-hidden="true"></span>
					<span class="hidden">Movie Genre</span>
				</label>
				<input type="text" name="genre" placeholder="Movie Genre *"  maxlength="255" required>
			</div>
			<div class="rating">
				<label>
					<span class="fa fa-star" aria-hidden="true"></span>
					<span class="hidden">Movie Rating</span>
				</label>
				<select name="rating">
					<option value="" disabled selected>Movie Rating *</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</div>
			<div class="description">
				<label>
					<span class="fa fa-commenting" aria-hidden="true"></span>
					<span class="hidden">Movie Description</span>
				</label>
				<textarea name="description" maxlength="65535" placeholder="Movie Description *" required></textarea>
			</div>
			<div class="cover">
				<label>
					<span class="fa fa-picture-o" aria-hidden="true"></span>
					<span class="hidden">Movie Cover</span>
				</label>
				<input type="file" name="cover" placeholder="Movie Cover *" required>
			</div>
			<div class="submit">
				<input class="add" type="submit" name="submit" value="Add a movie">
			</div>
		</form>
    </section>
</body>
</html>