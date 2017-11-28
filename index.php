<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Image vacuum</title>
    <meta name="description" content="Small page to vacuum images of an other page via its URL">
  </head>
  <body>
    <form>
      <input type="text" name="url">
      <input type="submit" value="envoyer">
    </form>

    <?php
      // Path of upload/ directory
      $uploadDirectoryPath = "upload/";
      // Regular expression of a URL (incomplete)
      $regexURL = "#^https?:\/\/([a-z\d-_]{2,}\.){1,}[a-z]{2,20}\/?#i";
      // Regular expression of an image file
      $regexImages = "#([^\"']*\.(jpe?g|a?png|bmp|tiff|svg))#i";
      // Variable containing the URL passed
      $url = $_GET["url"];

      // Create a new directory "upload/" if it doesn't exist already
      if (!file_exists($uploadDirectoryPath)) {
        mkdir($uploadDirectoryPath);
      } else {
        // Grab all files inside upload/ and delete them with unlink($filename)
        foreach (glob($uploadDirectoryPath . '*.*') as $file) {
          unlink($file);
        }
      }

      // Check url syntax before anything
      if (preg_match($regexURL, $url)) {

        // Initialize a new CURL session with the URL given
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // Ask to return the transfert result instead of displaying
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Start a new cookie session to avoid conflicts with cookies from previous sessions
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        // Get the page source (doesn't display because of CURLOPT_RETURNTRANSFER option)
        $source = curl_exec($ch);
        // Look for all images in $source and put it in an array $matches
        preg_match_all($regexImages, $source, $matches);

        // Get all images in $matches and put inside upload/ directory
        foreach ($matches[0] as $image) {
          // Add @file_get_contents() instead of file_get_contents() to prevent
          // warning messages with some pages like https://twitter.com/stephane_hary
          $temp = @file_get_contents($image);
          // Rename files with uniqid() and force extention type to be .png
          file_put_contents($uploadDirectoryPath . uniqid() . ".png", $temp);
        }

        echo "All images from " . $url . " have been successfully downloaded!";

        // Close CURL session
        curl_close($ch);
      } else {
        die("Please enter a valid URL");
      }
    ?>
  </body>
</html>
