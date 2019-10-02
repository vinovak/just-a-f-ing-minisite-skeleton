<?php
/**
* This is a part of the "Just A F-ing Mini Skeleton" project.
* GitHub: https://github.com/vinovak/just-a-f-ing-minisite-skeleton
*
* This particular script is the simplest and easiest way I could
* thing of to handle very basic photogallery related operations.
*
* It generates thumbnails when missing, to have them physicaly
* on the server in given directory for all the crop mode + supporter
* size combinations.
*
* It does that on the fly, when http request commes. So when there
* is a lot ofcombinations and/or a lot of pictures, it may time out.
* This timeout should have no side effects, so you can keep calling
* it untill all the thumbnails are pregenerated. Therefor, if you're
* not able to set the time limit a bit longer, I suggest calling
* this script separately by hand when ever you add new fullsize
* pictures and/or supported crop modes and sizes.
*
* It might sound very lame, but right now it's all I need. It has
* absolutely zero ambition to do more than it does right now.
* So if it does not suit your needs, you are going to have to
* improve it on you self, or just find another solution.
*
* Oh and it needs write permissions inside the gallery folder.
*
* Oh and Imagick for PHP available on server.
*/

/**
* If you turn debug mode on, exceptions and errors will be outputted.
* You will also gain the ability to use ?prune=1 GET parameter to
* delete all the pregenerated thumbnails.
*/
const DEBUG_MODE = false;

/**
* Transforming errors to exceptions
*/
function exception_error_handler($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}

if (DEBUG_MODE) {
  set_error_handler("exception_error_handler");
} else {
  if ((isset($_GET['prune']) && $_GET['prune'] == 1) || (isset($_GET['skipThumbnailGeneration']) && $_GET['skipThumbnailGeneration'] == 1)) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(500);
    echo json_encode(array("status_code: " => 500, "message" => "Prune and/or skipThumbnailGeneration is set in GET request, but DEBUG_MODE is off. You can not do those without DEBUG_MODE enabled."));
    exit;
  }
}

/**
* Configure stuff here.
*/
const GALLERY_PATH = 'gallery/';
const RESIZED_STRING_PREFIX = '-rto-'; //can be anything. Just so you can distinguish generated files from originals
const SUPPORTED_THUMB_SIZES = array('400x250', '250x250');
const SUPPORTED_MODES = array('fit', 'fitHeight'); //other possibilities: 'fill', 'fitWidth'
const SUPPORTED_EXTENSIONS = array('jpg', 'jpeg', 'png');
const SIZE_CACHE_FILE_PATH = GALLERY_PATH . 'size-cache.txt';
const CACHE_DATA_DELIMITER = '/';
const CACHE_ITEM_DELIMITER = "\n";

function generateRegexString()
{
  $strings = [];
  foreach (SUPPORTED_THUMB_SIZES as $size) {
    foreach (SUPPORTED_MODES as $mode) {
      $strings[] = preg_quote(RESIZED_STRING_PREFIX . $mode . '-' . $size);
    }
  }

  return implode('|', $strings);
}

function isOriginalSize($fileName)
{
  $matches = [];
  preg_match('/^.*(' . generateRegexString() . ')\.(' . implode('|', SUPPORTED_EXTENSIONS) . ')$/i', $fileName, $matches);
  if (count($matches) > 2) {
    return false;
  }
  return true;
}

function hasSupportedExtension($fileName)
{
  $matches = [];
  preg_match('/^(.*)\.(' . implode('|', SUPPORTED_EXTENSIONS) . ')$/im', $fileName, $matches);
  if (sizeof($matches) === 3) {
    return true;
  }

  return false;
}

function getImageDimensions($imageFileName)
{
  $image = new Imagick(GALLERY_PATH . $imageFileName);
  return $image->getImageGeometry();
}

function resizeToFit($image, $width, $height)
{
  return $image->thumbnailImage((int)$width, (int)$height, true);
}

function resizeToFitHeight($image, $height)
{
  return $image->thumbnailImage(0, (int)$height);
}

function resizeToFitWidth($image, $width)
{
  return $image->thumbnailImage((int)$width, 0);
}

function resizeToFill($image, $width, $height)
{
  return $image->cropThumbnailImage((int)$width, (int)$height);
}

function generateFileName($originalFileName, $modeString, $sizeString)
{
  $parsedFilename = [];
  preg_match('/^(.*)\.(' . implode('|', SUPPORTED_EXTENSIONS) . ')$/i', $originalFileName, $parsedFilename);
  $filenameUpToExtension = $parsedFilename[1];
  $extension = $parsedFilename[2];

  return $filenameUpToExtension . RESIZED_STRING_PREFIX . $modeString . '-' . $sizeString . '.' . $extension;
}

function generateThumbnails($originalFileName)
{
  $originalImage = new Imagick(GALLERY_PATH . $originalFileName);
  foreach (SUPPORTED_THUMB_SIZES as $size) {
    foreach (SUPPORTED_MODES as $mode) {
      $thumbnailFileName = GALLERY_PATH . generateFileName($originalFileName, $mode, $size);

      if (file_exists($thumbnailFileName)) {
        continue;
      }

      $imageResized = clone $originalImage;

      $width = substr($size, 0, strpos($size, 'x'));
      $height = substr($size, strpos($size, 'x') + 1);

      if ($mode === 'fit') {
        resizeToFit($imageResized, $width, $height);
      } elseif ($mode === 'fitHeight') {
        resizeToFitHeight($imageResized, $height);
      } elseif ($mode === 'fitWidth') {
        resizeToFitWidth($imageResized, $width);
      } elseif ($mode === 'fill') {
        resizeToFill($imageResized, $width, $height);
      }

      $imageResized->writeImage($thumbnailFileName);
    }
  }
}

function getThumbnails($originalFileName)
{
  $thumbnails = [];
  foreach (SUPPORTED_THUMB_SIZES as $size) {
    foreach (SUPPORTED_MODES as $mode) {
      $thumbnailFileName = generateFileName($originalFileName, $mode, $size);
      if (file_exists(GALLERY_PATH . $thumbnailFileName)) {
        $thumbnails[$mode][$size] = $thumbnailFileName;
      } else {
        $thumbnails[$mode][$size] = $originalFileName;
      }
    }
  }

  return $thumbnails;
}

function getSizesFromCache()
{
  $sizes = [];
  if (file_exists(SIZE_CACHE_FILE_PATH)) {
    $cache = file_get_contents(SIZE_CACHE_FILE_PATH);
    $cacheItems = explode(CACHE_ITEM_DELIMITER, $cache);

    foreach ($cacheItems as $item) {
      if (empty($item)) {
        continue;
      }

      $itemArray = explode(CACHE_DATA_DELIMITER, $item);
      $sizes[$itemArray[0]] = [$itemArray[1],$itemArray[2]];
    }
  }

  return $sizes;
}

function appendSizesToCache($imageIdentifier, $width, $height)
{
  $cacheItemString = $imageIdentifier . CACHE_DATA_DELIMITER . $width . CACHE_DATA_DELIMITER . $height . CACHE_ITEM_DELIMITER;
  file_put_contents(SIZE_CACHE_FILE_PATH, $cacheItemString, FILE_APPEND);
}

function getCahcedOrCacheImageSize(&$currentCache, $imageFileName)
{
  if (!isset($currentCache[$imageFileName])) {
    $dimensions = getImageDimensions($imageFileName);
    appendSizesToCache($imageFileName, $dimensions['width'], $dimensions['height']);
    $currentCache[$imageFileName] = [$dimensions['width'], $dimensions['height']];
  }

  return $currentCache[$imageFileName];
}

function flushSizesCache()
{
  if (file_exists(SIZE_CACHE_FILE_PATH)) {
    unlink(SIZE_CACHE_FILE_PATH);
  }
}

try {
  $fileNames = scandir(GALLERY_PATH);
  $prune = (DEBUG_MODE && (isset($_GET['prune']) && $_GET['prune']) == 1) ? true : false;
  $skipThumbnailGeneration = (DEBUG_MODE && (isset($_GET['skipThumbnailGeneration']) && $_GET['skipThumbnailGeneration']) == 1) ? true : false;

  $sizesCache = [];
  $images = [];

  if ($prune) {
    flushSizesCache();

    foreach ($fileNames as $fileName) {
      if (!is_dir(GALLERY_PATH . $fileName) && hasSupportedExtension($fileName)) {
        if (!isOriginalSize($fileName)) {
          unlink(GALLERY_PATH . $fileName);
        }

        $fileNames = scandir(GALLERY_PATH);
      }
    }
  }

  $sizesCache = getSizesFromCache();
  foreach ($fileNames as $fileName) {
    if (is_dir(GALLERY_PATH . $fileName) || !hasSupportedExtension($fileName)) {
      continue;
    }

    if (isOriginalSize($fileName)) {
      if (!$skipThumbnailGeneration) {
        generateThumbnails($fileName);
      }

      $size = getCahcedOrCacheImageSize($sizesCache, $fileName);

      $images[] = array(
        'original' => $fileName,
        'width' => $size[0],
        'height' => $size[1],
        'thumbnails' => getThumbnails($fileName),
      );

    }
  }

  header("Content-Type: application/json; charset=UTF-8");
  echo json_encode($images);
} catch (Exception $e) {

  if (DEBUG_MODE) {
    throw $e;
  }

  header("Content-Type: application/json; charset=UTF-8");
  http_response_code(500);
  echo json_encode(array("status_code: " => 500, "message" => "Something got f-... Went wrong."));
  exit;
}
