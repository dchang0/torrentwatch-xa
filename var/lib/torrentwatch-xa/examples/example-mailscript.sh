#!/bin/sh
# Example Trigger email shell script that can be run by torrentwatch-xa.
# Copy and then modify this script and make sure that www-data has rwx permission
# on it and can reach its path. Then specify the location of this script in the
# Configure > Notify > Script field.
#
# To customize below:
# Edit the FROM and TO lines to add your email address
# Set MAILSERVER to the FQDN or IP address of your SMTP mail server that will 
# accept outgoing email.
#
# You are welcome to customize the script as much as you like.

MAILSERVER=""
FROM=""
TO=""
TEMPFILE="/tmp/twxamail"

rm -f $TEMPFILE
echo "To: "${TO} > $TEMPFILE
echo "From: "${FROM} >> $TEMPFILE

sendemail ()
{
/usr/sbin/sendmail -S $MAILSERVER -f $FROM -t <$TEMPFILE 
}

case $1 in
    favstart)
        # This block runs when a Favorite has started downloading
	echo "Subject: torrentwatch-xa has downloaded $2" >> $TEMPFILE
	echo "" >> $TEMPFILE	
	echo "Downloaded $2 $3 $4" >> $TEMPFILE
	sendemail
        ;;
    nonfavstart)
        # This block runs when a non-Favorite has started downloading
	echo "Subject: torrentwatch-xa has downloaded $2" >> $TEMPFILE
	echo "" >> $TEMPFILE	
	echo "Downloaded $2 $3 $4" >> $TEMPFILE
	sendemail
        ;;
    error)
        # This block runs when an error occurs
	echo "Subject: torrentwatch-xa has an error $2" >> $TEMPFILE
	echo "" >> $TEMPFILE	
	echo "More Info : $3 $4" >> $TEMPFILE
	sendemail 
        ;;
esac
rm -f $TEMPFILE



