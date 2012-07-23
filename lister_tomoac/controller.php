<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

class ListerTomoacPackage extends Package {

     protected $pkgHandle = 'lister_tomoac';
     protected $appVersionRequired = '5.4.0';
     protected $pkgVersion = '0.2.4.1';

     public function getPackageDescription() {
          return t('Database Lister by tomoac');
     }

     public function getPackageName() {
          return t('Database Lister');
     }
     
	public function install() {
		$pkg = parent::install();

		// install block 
		BlockType::installBlockTypeFromPackage('lister_tomoac', $pkg); 
		Loader::model('single_page');
/*
		// install pages
		$sp1 = SinglePage::add('/dashboard/lister_tomoac', $pkg);
		$sp1->update(array('cName'=>t('Database Lister'), 'cDescription'=>t('Database Lister by tomoac')));

		$sp2 = SinglePage::add('/dashboard/lister_tomoac/onetoone', $pkg);
		$sp2->update(array('cName'=>t('Onetoone'), 'cDescription'=>t('Onetion of database lister by tomoac')));
*/
	}
}
