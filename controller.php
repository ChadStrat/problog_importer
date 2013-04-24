<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class ProblogImporterPackage extends Package {

	protected $pkgHandle = 'problog_importer';
	protected $appVersionRequired = '5.6.0';
	protected $pkgVersion = '0.0.0.2';
	
	public function getPackageDescription() {
		return t("A Blog Importing addon");
	}
	
	public function getPackageName() {
		return t("ProBlog Importer");
	}
	
	public function install() {
		$this->precheck();
		$this->load_required_models();
		$pkg = parent::install();
		$cp = SinglePage::add('/dashboard/problog/site_importer/', $pkg);
		$cp->update(array('cName'=>t('ProBlog Importer'), 'cDescription'=>t('Import XML Blog Data')));
	}
	
	function precheck(){
    	$pk = Package::getByHandle('problog');
     	if(!$pk) {
      		throw new Exception(t('You must have <a href="http://www.concrete5.org/marketplace/addons/problog/">ProBlog</a> installed prior to installing this addon.'));  
     	 	exit;
    	}
    }

	function load_required_models() {
		Loader::model('single_page');
		Loader::model('collection');
		Loader::model('page');
		loader::model('block');
		Loader::model('collection_types');
		Loader::model('/attribute/categories/file');
		Loader::model('/attribute/categories/collection');
		Loader::model('/attribute/categories/user');
		Loader::model('/attribute/types/select/controller');
	}	
}