<?php
///////////////////////////////////////////////////
// Paradise ~ centerkey.com/paradise             //
// GPLv3 ~ Copyright (c) individual contributors //
///////////////////////////////////////////////////

// Startup
// Initializes the data folder

$defaultSettingsDb = array(
   "title" =>          "My Gallery",
   "title-font" =>     "Reenie Beanie",
   "title-size" =>     "400%",
   "subtitle" =>       "Photography &bull; Art Studio",
   "footer" =>         "Copyright &copy; " . gmdate("Y"),
   "caption-caps" =>   false,
   "caption-italic" => true,
   "cc-license" =>     false,
   "bookmarks" =>      true,
   "contact-email" =>  "",
   "pages" => array(
      array("name" => "gallery", "title" => "Gallery", "show" =>  true),
      array("name" => "artist",  "title" => "Artist",  "show" =>  false),
      array("name" => "contact", "title" => "Contact", "show" =>  false)
      )
   );
$defaultAccountsDb = array(
   "users" =>   json_decode("{}"),  //email -> created (epoch), hash, enabled (boolean)
   "invites" => json_decode("{}")   //inviteCode -> from, to, expires (epoch), accepted (epoch)
   );

function setupDataFolder($dataFolder, $name) {
   $folder =      "{$dataFolder}/{$name}";
   $defaultView = "{$dataFolder}/index.html";
   if (!is_dir($folder) && !mkdir($folder))
      exit("Unable to create data folder: {$folder}");
   initializeFile($defaultView, "Nothing to see.");  //TODO: cover all folders
   }

function setupInstallKey($folder) {
   $fileSearch = glob("{$folder}/key-*.txt");
   if (count($fileSearch) === 0) {
      $fileSearch[] = "{$folder}/key-" . mt_rand() . mt_rand() . mt_rand() . ".txt";
      touch($fileSearch[0]);
      }
   preg_match("/key-(.*)[.]txt/", $fileSearch[0], $matches);
   return $matches[1];
   }

function setupDb($dbFilename, $defaultDb) {
   initializeFile($dbFilename, json_encode($defaultDb));
   }

function setupCustomCss($dataFolder) {
   $defaultCss = array(
      "/*  Paradise PHP Photo Gallery                                */",
      "/*  Edit this CSS file to customize the look of the gallery.  */",
      "/*  Put custom images in: gallery/~data~/graphics             */",
      "",
      "body { color: whitesmoke; background-color: dimgray; }",
      "body >footer { background-color: gray; border-color: black; }",
      ".gallery-images .image img { border-color: black; }"
      );
   $filename = "{$dataFolder}/custom-style.css";
   initializeFile($filename, implode(PHP_EOL, $defaultCss));
   }

function setupCustomPage($dataFolder, $pageName) {
   $filename = "{$dataFolder}/page-{$pageName}.html";
   if (!file_exists($filename)) {
      $defaultHtml = "<h3>This page is under construction.</h3>\n<hr>\nEdit: ";
      touch($filename);
      file_put_contents($filename, $defaultHtml . realpath($filename) . PHP_EOL);
      }
   }

function workaroundToUpgradePortfolio() {
   global $portfolioFolder;
   foreach (glob("{$portfolioFolder}/*-db.json") as $dbFilename) {
      $db = readDb($dbFilename);
      $db->sort =     isset($db->sort) ? $db->sort : intval($db->id) * 10000;
      $db->original = isset($db->original) ? $db->original : $db->{"original-file-name"};
      $db->uploaded = isset($db->uploaded) ? $db->uploaded : $db->{"upload-date"};
      $db->display =  isset($db->display) ? $db->display === "on" || $db->display === true : true;
      saveDb($dbFilename, $db);
      }
   logEvent("portfolio-upgrade-done", "last-image", $db->id, $db);
   }

foreach(array("", "graphics", "portfolio", "uploads") as $name)
   setupDataFolder($dataFolder, $name);
$installKey = setupInstallKey($dataFolder);
$settingsDbFile =  "{$dataFolder}/settings-db.json";
$galleryDbFile =   "{$dataFolder}/gallery-db.json";
$accountsDbFile =  "{$dataFolder}/accounts-db-{$installKey}.json";
$uploadsFolder =   "{$dataFolder}/uploads";
$portfolioFolder = "{$dataFolder}/portfolio";
$galleryFolder =   "{$dataFolder}/gallery";
setupDb($settingsDbFile, $defaultSettingsDb);
setupDb($accountsDbFile, $defaultAccountsDb);
setupCustomCss($dataFolder);
setupCustomPage($dataFolder, $defaultSettingsDb["pages"][1]["name"]);
generateGalleryDb();
?>
