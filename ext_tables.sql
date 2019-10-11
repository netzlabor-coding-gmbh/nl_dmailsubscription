#
# Add SQL definition of database tables
#

#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
  tx_nldmailsubscription_confirmed_at INT(10) UNSIGNED NOT NULL DEFAULT '0',
	tx_nldmailsubscription_token_identifier varchar(255) DEFAULT '' NOT NULL,
  tx_nldmailsubscription_token_expires INT(10) UNSIGNED DEFAULT '0'
);