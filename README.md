# Just A F-ing Minisite Skeleton
or JAFMS for shrt.

The most simple responsive single page minisite or microsite template. Built on Twitter Bootstrap, all dependencies included, no compilation required. F-k it, it even works without PHP, if you don't need dynamic photo gallery handling. Pure static html + css + js website.

Live example is currently available at [https://just-an-f-ing-microsite-skeleton-vtnovk529892.codeanyapp.com/](https://just-an-f-ing-microsite-skeleton-vtnovk529892.codeanyapp.com/).

## What is it for?
It's for when you need to make a f-ing nice looking mobile-first responsive single page micro/mini-site for your friend or whoever, but you do it like once or twice a year, so you don't have any tools for proper development up to date, so you ain't gonna be compiling lesses/sasses on boilerplates bowers gulps grunts f-ing shts.

Or you are just too old (or simply can't be fucking bothered) to keep up with all that. (My case)

It's a web page. No f-ing rocket science.

## What do I do with it?
You just grab it from here, copy it where the f-k ever you want it, delete everything you don't need, copy the sht you need as many times as you need, change some texts, pictures colors and sht and voila - you've got you self a f-ing simple single page website in a f-ing blink of an eye.

## Well that sounds f-ing awesome! What do I need to make it work?
Are you f-ing stupid or something? Do you read what I write? You need nothing. Everything is packed in this repo already.

## So what's in the repo then?
The whole f-ing thing is built on [Twitter Bootstrap](https://getbootstrap.com/). So a local copy is included. Also the photo gallery utilizes [Justified Gallery](https://miromannino.github.io/Justified-Gallery/) and [PhotoSwipe](https://photoswipe.com/), also included. And let's not forget [Font Awesome](https://fontawesome.com/). And [jQuery](https://jquery.com/). Shut up.

## Why is it all in the repo and not included on compilation or something?
Well because there is no f-ing compilation or something, is there!? I was tired of that sht. I just want to grab code, change a few strings and pictures and make it online. I don't do it on daily basis, I don't have my environment set up for this sht and I don't want to be doing that. Ain't no one got time for that sht.

## Do I need to configure someting to make it run?
I strongly suggest to use absolute URIs. Since this is very static thing, you would need to go through the code and manualy insert your base URI to where ever needed. Do that. And I'm talking specificaly about index.html and js/main.js.

## Regarding PHP
There IS the option of using some photo gallery handling on the server side. If you have PHP (I guess 5.6+ would do, but you're f-ed in your head if you don't have at least 7 already), you can go check out how gallery-server.php works. If not, and you're ok with hand-made static gallery, just cut that ajax sht off and hard-code it the old way.

## Will there be more documentation/tutorials on how to make this work?
Well, not by me, that's for sure. As far as I'm concerned, this matter is so simple, you can just look into the four source files (index.html, js/main.js, css/style.css and gallery-server.php) and if you're not completely brain-dead, you have to be able to find out wtf is going on there.

## Can I contribute?
Yes you can, but beware: NO F-ING FANCY STUFF! No f-ing compilation, deploy, automation, building, making, package management... NO! This thing needs to be kept as simple and as independent on environment (both dev and production) as f-ing possible.

## I don't like --something-- about his project.
Well, I most probably don't give a f-k. You can either put up with it, not use it, or **GO FORK YOUR SELF**.