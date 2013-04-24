<?php 
defined('C5_EXECUTE') or die(_("Access Denied.")); 
class DashboardProblogSiteImporterController extends Controller {

	public function run_routine($routine){
	
		$location = $_REQUEST['importLocation'];
		$theme = $_REQUEST['selectedTheme'];
		$type = $_REQUEST['source_type'];
		$path = DIR_BASE.File::getRelativePathFromID($_REQUEST['importXml']);
		
		Loader::model('site_importer','site_importer');
		$si = New SiteImporter($path,$type,$location,$theme);
		
		$jsx = Loader::helper('json');
		$js = new stdClass;
		
		try {
			$si->$routine();
			$js->error = false;
		} catch(Exception $e) {
			$js->error = true;
			$js->message = $e->getMessage();
		}
		
		print $jsx->encode($js);
		exit;
	}
	
	public function get_post_count(){
		$path = DIR_BASE.File::getRelativePathFromID($_REQUEST['importXml']);
		$xmlObject = simplexml_load_file($path,'SimpleXMLElement');
		//var_dump(count($xmlObject->channel->item));
		print json_encode(count($xmlObject->channel->item));
		exit;
	}
	
	public function run_import_item(){
		Loader::model($_REQUEST['importType'],'problog_importer');
		$location = $_REQUEST['importLocation'];
		$ctID = $_REQUEST['selectedPageType'];
		$path = DIR_BASE.File::getRelativePathFromID($_REQUEST['importXml']);
		$xmlObject = simplexml_load_file($path,'SimpleXMLElement');
		$i = $_REQUEST['i'];

		foreach($xmlObject->channel->item as $item_object){
			
			$t++;
			if($t==$i){
				$method = ucfirst($_REQUEST['importType']).'Item';
				$item = new $method($item_object);
				//var_dump($item);exit;
				$page = new CreateBlogPost($item,$location,$ctID);
				//print json_encode($child);
				if($page){
					print json_encode(array('success'=>1));
				}else{
					print json_encode(array('error'=>t('There was a problem with your import.')));
				}
			}
		}
		
		exit;
	}
	
}