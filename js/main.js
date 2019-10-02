/**
*
* I know this is a bit of a mess. I'm not very confortable with JavaScript
* and it's very obvious from this code.
*
* I tried to document it a little bit with comments. If anyone fancys
* to make this cleaner and more readable, be my guest.
*
* GitHub: https://github.com/vinovak/just-a-f-ing-minisite-skeleton
*
*/

/**
* This makes the header overlay dissapear when scrolled.
*/
$(window).scroll(function(){
    $("#intro-box").css("opacity", 1 - $(window).scrollTop() / 200);
});

/**
* Triggers when user clicks on thumbnail in gallery.
*/
function onThumbnailsClick(e) {
    e = e || window.event;
    e.preventDefault ? e.preventDefault() : e.returnValue = false;

    var index = e.getAttribute("data-pswp-uid");


    if(index >= 0) {
        // open PhotoSwipe if valid index found
        openPhotoSwipe(index, false, false, e);
    }
    return false;
}

/**
* Next block of code is PhotoSwipe implementation as grabbed from their doc.
* https://photoswipe.com/
*/
var galleryItems = [];
function openPhotoSwipe(index, disableAnimation, fromURL, thumbnailElement) {
    var pswpElement = document.querySelectorAll('.pswp')[0],
        gallery,
        options,
        items;

    items = galleryItems;

    // define options (if needed)
    options = {

        // define gallery index (for URL)
        galleryUID: 1,

        getThumbBoundsFn: function(index) {
            // See Options -> getThumbBoundsFn section of documentation for more info
            var thumbnail = thumbnailElement, // find thumbnail
                pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                rect = thumbnail.getBoundingClientRect();

            return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
        }

    };

    // PhotoSwipe opened from URL
    if(fromURL) {
        if(options.galleryPIDs) {
            // parse real index when custom PIDs are used
            // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
            for(var j = 0; j < items.length; j++) {
                if(items[j].pid == index) {
                    options.index = j;
                    break;
                }
            }
        } else {
            // in URL indexes start from 1
            options.index = parseInt(index, 10) - 1;
        }
    } else {
        options.index = parseInt(index, 10);
    }

    // exit if index not found
    if( isNaN(options.index) ) {
        return;
    }

    if(disableAnimation) {
        options.showAnimationDuration = 0;
    }

    // Pass data to PhotoSwipe and initialize it
    gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
    gallery.init();
}

/**
* Here we build the gallery out of data received from gallery-server.php.
* Unstable individuals might want to look away now.
*/
function showGallery(data) {
  var item;

  for (i = 0; i < Object.keys(data).length; i++) {
    var newGalleryItem = '<a href="gallery/' + data[Object.keys(data)[i]].original + '" onclick="return onThumbnailsClick(this);" data-size="' + data[Object.keys(data)[i]].width + 'x' + data[Object.keys(data)[i]].height + '" data-pswp-uid="' + i + '">';
    newGalleryItem += '<img src="gallery/' + data[Object.keys(data)[i]].thumbnails.fitHeight['250x250'] + '" itemprop="thumbnail">';
    newGalleryItem += '</a>';

    item = {
        src: 'gallery/' + data[Object.keys(data)[i]].original,
        w: data[Object.keys(data)[i]].width,
        h: data[Object.keys(data)[i]].height,
        msrc: 'gallery/' + data[Object.keys(data)[i]].thumbnails.fit['400x250']
    };
    galleryItems.push(item);

    $( "#gallery-photos-container" ).append(newGalleryItem);
  }
  document.getElementById('gallery-preload-bar').classList.add("d-none");
  $("#gallery-photos-container").justifiedGallery({
    rowHeight : 250,
    lastRow : 'nojustify',
    margins : 5
  });
}

/**
* Unhides error bar, hides loading bar for photo gallery.
*/
function showGalleryErrorMessage() {
  document.getElementById('gallery-preload-bar').classList.add("d-none");
  document.getElementById('gallery-error-bar').classList.remove("d-none");
}

/**
* Makes request to obtain photogallery data from gallery-server.php.
*/
function loadGallery () {
  $.ajax({
    url: 'gallery-server.php',
    type: 'POST',
    dataType: 'json',
    success: function(data, text, xhr){
      showGallery(data);
      return false;
    },
    error:function(data, text, error){
      showGalleryErrorMessage();
      return false;
    }
  });
}

/**
* Start loading the gallery on window.load.
*/
(function() {

  'use strict';
  window.addEventListener('load', function() {
    loadGallery();
  }, false);
})();