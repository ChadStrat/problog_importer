<?php   defined('C5_EXECUTE') or die("Access Denied.");
loader::library('content/importer');
set_time_limit(120);


class CreateBlogPost extends Model {

	var $wp_post;
	var $location;
	var $ctID;
	var $path;
	
	public function __construct($item,$location,$ctID){
		$this->location = $location;
		$this->ctID = $ctID;
		$this->wp_post = $item;
		if($this->check_for_page() < 1){
			$p = $this->createPage();
			if($p->cID > 0){
				$this->setCategories($p);
				$this->setTags($p);
				$this->setAuthor($p);
				$this->setContent($p);
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function check_for_page(){
		Loader::model('page');
		$parent = Page::getByID($this->location);
		$ct = CollectionType::getByID($this->ctID);
		$this->path = $parent->getCollectionPath().'/'.$this->wp_post->page_title.'/';
		$page = Page::getByPath($path);
		if($page->cID > 0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	public function createPage(){
		Loader::model('page');
		$parent = Page::getByID($this->location);
		$ct = CollectionType::getByID($this->ctID);
		$data = array(
			'cName' => $this->wp_post->title, 
			'cHandle' =>  $this->wp_post->page_title,
			'cDescription' => $this->wp_post->description, 
			'cDatePublic' => $this->wp_post->pubDate
		);
		$p = $parent->add($ct, $data);
		return $p;
	}
	
	public function setCategories($p){
		$ak = CollectionAttributeKey::getByHandle('blog_category');
		if(is_array($this->wp_post->categories)){
			foreach($this->wp_post->categories as $option){
				$db = Loader::db();
				$avID = $db->getOne("SELECT ID FROM atSelectOptions WHERE value = ? AND akID = ?",array($option,$ak->akID));
				if(!$avID){
					$db->execute("INSERT INTO atSelectOptions (value,akID) VALUES (?,?)",array($option,$ak->akID));
				}
			}
			$p->setAttribute($ak,$this->wp_post->categories);
		}
	}
	
	public function setTags($p){
		$ak = CollectionAttributeKey::getByHandle('tags');
		if(is_array($this->wp_post->tags)){
			foreach($this->wp_post->tags as $option){
				$db = Loader::db();
				$avID = $db->getOne("SELECT ID FROM atSelectOptions WHERE value = ? AND akID = ?",array($option,$ak->akID));
				if(!$avID){
					$db->execute("INSERT INTO atSelectOptions (value,akID) VALUES (?,?)",array($option,$ak->akID));
				}
			}
			$p->setAttribute($ak,$this->wp_post->tags);
		}
	}
	
	public function setAuthor($p){
		$ak = CollectionAttributeKey::getByHandle('blog_author');
		if($this->wp_post->author){
			Loader::model('userinfo');
			$ui = UserInfo::getByUserName($this->wp_post->author);
			if(is_object($ui)){
				$p->setAttribute($ak,$ui->getUserID());
			}
		}
	}
	
	public function setContent($p){
		$bt = BlockType::getByHandle('content');
		$content = $this->wp_post->content;
		$content = str_replace("\n",'<br/>',$content);
		$data = array('content' => $content);		
		$p->addBlock($bt, 'Main', $data);
        $block = $p->getBlocks('Main');
		foreach($block as $b) {
			if($b->getBlockTypeHandle()=='content'){
				$b->setCustomTemplate('blog_post');
				$b->setBlockDisplayOrder('+1');
				$b->setBlockDisplayOrder('+1');
			}
		}
	}
}


class WpItem extends Model {

	var $categories;
	var $tags;
	var $description;
	var $pubDate;
	var $title;
	var $author;
	var $page_title;
	var $content;

	public function __construct($item){

		$this->set_item_categories($item);
		$this->set_item_tags($item);
		$this->set_item_description($item);
		$this->set_item_pubDate($item);
		$this->set_item_title($item);
		$this->set_page_title($item);
		$this->set_page_author($item);
		$this->set_item_content($item);
	}
	
	public function set_item_categories($item){
		$vars = array();
		if(is_object($item->category)){
			foreach($item->category as $cat){
				if((string)$cat['domain'] == 'category'){
					$vars[] = (string)$cat['nicename'];
				}
			}
			$this->categories = $vars;
		}
	}
	
	public function set_item_tags($item){
		$vars = array();
		if(is_object($item->category)){
			foreach($item->category as $cat){
				if((string)$cat['domain'] == 'post_tag'){
					$vars[] = (string)$cat['nicename'];
				}
			}
			$this->tags = $vars;
		}	
	}
	
	public function set_item_description($item){
		$this->description = (string)$item->description;
		if(!$this->description){
			$namespaces = $item->getNamespaces(true);
			$child = $item->children($namespaces['excerpt']);
			$this->description = (string)$child->encoded;
		}
	}
	
	public function set_item_pubDate($item){
		$this->pubDate = date('Y-m-d H:i:s',strtotime((string)$item->pubDate));
	}
	
	public function set_item_title($item){
		$this->title = (string)$item->title;
	}
	
	public function set_page_title($item){
		$namespaces = $item->getNamespaces(true);
		$child = $item->children($namespaces['wp']);
		$this->page_title = (string)$child->post_name;
	}
	
	public function set_page_author($item){
		$namespaces = $item->getNamespaces(true);
		$child = $item->children($namespaces['dc']);
		$this->author = (string)$child->creator;
	}
	
	public function set_item_content($item){
		$namespaces = $item->getNamespaces(true);
		$child = $item->children($namespaces['content']);
		$this->content = (string)$child->encoded;
	}
	
	public function parse_image($image_url){
		
	}
	
	public function parse_link($link){
		
	}

}