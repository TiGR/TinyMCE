#!/bin/sh

dir=`pwd`
name=`basename \`pwd\``

if [ -e "/tmp/$name" ]; then
    echo "/tmp/$name exists, can not continue."
    exit 1
fi;

if [ -e "$name.zip" ]; then
    rm $name.zip
fi;
mkdir /tmp/$name/
cp -Ra ../$name /tmp/
rm /tmp/$name/build.sh
rm -rf /tmp/$name/.git/

cd /tmp
zip -rq ./$name.zip $name/
rm -rf $name
mv -f $name.zip $dir