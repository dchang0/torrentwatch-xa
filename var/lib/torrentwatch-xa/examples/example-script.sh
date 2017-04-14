#!/bin/sh
# Example Trigger shell script that can be run by torrentwatch-xa.
# Copy and then modify this script and make sure that www-data has rwx permission
# on it and can reach its path. Then specify the location of this script in the
# Configure > Notify > Script field.
#
# You are expected to write DIY code in each of the three blocks below. Sample
# commands are provided, but they likely will not want to keep them.
case $1 in
    favstart)
        # This block runs when a Favorite has started downloading
        curl -f -s -S -u <user>:<pass> --data-urlencode "text=torrentwatch-xa started downloading $2" --data-urlencode "user=< recipient >" http://twitter.com/direct_messages/new.xml
        ;;
    nonfavstart)
        # This block runs when a non-Favorite has started downloading
        echo "Somebody is downloading $2" | wall
        ;;
    error)
        # This block runs when an error occurs
        echo "torrentwatch-xa encountered an error with $2 \n\n $3" | wall  
        ;;
esac

