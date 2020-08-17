#
# Add SQL definition of database tables
#

#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
  tx_nldmailsubscription_confirmed_at INT(10) UNSIGNED NOT NULL DEFAULT '0',
	tx_nldmailsubscription_token_identifier varchar(255) DEFAULT '' NOT NULL,
  tx_nldmailsubscription_token_expires INT(10) UNSIGNED DEFAULT '0',
  tx_nldmailsubscription_raffle int(11) unsigned DEFAULT '0',
  tx_nldmailsubscription_participation_confirmed_at INT(10) UNSIGNED DEFAULT '0',
  tx_nldmailsubscription_dataprocessing_confirmed_at INT(10) UNSIGNED DEFAULT '0',
);

#
# Table structure for table 'tx_nldmailsubscription_domain_model_raffle'
#
CREATE TABLE tx_nldmailsubscription_domain_model_raffle (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted smallint(5) unsigned DEFAULT '0' NOT NULL,
	hidden smallint(5) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state smallint(6) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,
	l10n_state text,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);