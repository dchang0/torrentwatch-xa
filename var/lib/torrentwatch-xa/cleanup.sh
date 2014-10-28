#!/bin/sh

cd `dirname $0`/web
pwd
for i in `find ./ -type f` ; do
  if [ -e "../$i" ] ; then rm ../${i} ; fi
done

cd ..
if [ -d css ] ; then rm -rf css/ ; fi
if [ -d php ] ; then rm rm -rf php/ ; fi
if [ -d templates ] ; then rm -rf templates/ ; fi
if [ -d javascript ] ; then rm -rf javascript/ ; fi
if [ -d images ] ; then rm -rf images/ ; fi
if [ -e info.php ] ; then rm info.php ; fi
if [ -e torrentwatch-xa.php ] ; then rm torrentwatch-xa.php ; fi
if [ -e index.html ] ; then rm index.html ; fi
if [ -e TWXRepository.xml ] ; then rm TWXRepository.xml ; fi
if [ -e Release-Checklist ] ; then rm Release-Checklist ; fi
if [ -e etc/torrentwatch-xa.config.dist ] ; then rm etc/torrentwatch-xa.config.dist ; fi
