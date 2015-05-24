#!/bin/sh
# Use this script to send emails when a favourite is matched, or an error occurs.
#
# ***** Make a copy of this file and save as mailscript.sh, if you dont do this it will get over-written with the next update! *****
#
# Edit the FROM and TO lines to add your email address
# Edit the MAILSERVER line to enter a mail server that will accept mail, this can either be your ISPs mail server, or the smtp server of your mail provider
#
# e.g aspmx2.googlemail.com should work for gmail for domains addresses.
# gmail-smtp-in.l.google.com.for all @gmail.com addresses
#
# You also need to edit Apps/torrentwatch-xa/etc/torrentwatch-xa.config and change the line:
#
# Script = 
# to
# Script = /share/Apps/torrentwatch-xa/mailscript.sh
#
MAILSERVER=""
FROM=""
TO=""
TEMPFILE="/tmp/mail.shizzle"

rm -f $TEMPFILE
echo "To: "${TO} > $TEMPFILE
echo "From: "${FROM} >> $TEMPFILE

sendemail ()
{
/usr/sbin/sendmail -S $MAILSERVER -f $FROM -t <$TEMPFILE 
}

case $1 in
    favstart)
	echo "Subject: torrentwatch-xa has downloaded $2" >> $TEMPFILE
	echo "" >> $TEMPFILE	
	echo "Downloaded $2 $3 $4" >> $TEMPFILE
	sendemail
        ;;
    nonfavstart)
        ;;
    error)
	echo "Subject: torrentwatch-xa has an error $2" >> $TEMPFILE
	echo "" >> $TEMPFILE	
	echo "More Info : $3 $4" >> $TEMPFILE
	sendemail 
        ;;
esac
rm -f $TEMPFILE



