<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class News extends Public_Controller {

	public $limit = 10; // TODO: PS - Make me a settings option
	
    function __construct() {
        parent::Public_Controller();
        
        $this->load->model('news_m');
        $this->load->module_model('categories', 'categories_m');
        $this->load->module_model('comments', 'comments_m');
        
        $this->load->helper('text');
        
        // All pages within news will display an archive list
        $this->data->archive_months = $this->news_m->getArchiveMonths();
        
    }

    // news/page/x also routes here
    function index() {
        
    	$this->data->pagination = create_pagination('news/page', $this->news_m->countArticles(), $this->limit, 3);
    	
    	$this->data->news = $this->news_m->getNews(array('limit' => $this->data->pagination['limit']));
        
        $this->layout->create('index', $this->data);
    }

    function category($category = 0) {
    	
    	if(!$category) redirect('news');
    	
    	// Count total news articles and work out how many pages exist
    	$this->data->pagination = create_pagination('news/category/'.$category, $this->news_m->countArticles(array(
    		'category'=>$category
    	)), $this->limit, 4);
    	
    	// Get category data
        $this->data->category = $this->categories_m->getCategory($category);
        
        // Get the current page of news articles
        $this->data->news = $this->news_m->getNews(array(
        	'category'=>$category, 
        	'limit' => $this->data->pagination['limit']
        ));
        
        // Set meta description based on article titles
        $meta_description = $this->_articles_metadata($this->data->news);
        
        // Build the page
        $this->layout->title('News | '.$this->data->category->title)
			
        	->set_metadata('description', $this->data->category->title.'. '.$meta_description)
        	->set_metadata('keywords', $this->data->category->title)
        	
        	->add_breadcrumb('News', 'news')
        	->add_breadcrumb($this->data->category->title)
        
        	->create('category', $this->data);
    }
    
    
    function archive($year = NULL, $month = '01') {
    	
    	if(!$year) $year = date('Y');
    	
        $month_date = new DateTime($year.'-'.$month.'-01');
        
    	$this->data->pagination = create_pagination('news/archive/'.$year.'/'.$month, $this->news_m->countArticles(array(
    		'year'=>$year,
    		'month'=>$month)
    	), $this->limit, 5);
    	
        $this->data->news = $this->news_m->getNews(array(
        	'year'=> $year,
        	'month'=> $month, 
        	'limit' => $this->data->pagination['limit']
        ));
        
        $this->data->month_year = $month_date->format("F 'y");
        
        // Set meta description based on article titles
        $meta_description = $this->_articles_metadata($this->data->news);
        
        $this->layout->title( $this->data->month_year, 'Archive', 'News' )
        	
        	->set_metadata('description', $this->data->month_year.'. '.$meta_description)
        	->set_metadata('keywords', $this->data->month_year)
        
        	->add_breadcrumb('News', 'news')
       		->add_breadcrumb('Archive: '.$month_date->format("F 'y"))
        	
       		->create('archive', $this->data);
    }
    
    // Public: View an article
    function view($slug = '') {
        
    	if (!$slug or !$article = $this->news_m->getArticle($slug, 'live'))
    	{
    		redirect('news/index');
    	}
    	
    	/*if($article->status != 'live' && !$this->user_lib->check_role('admin'))
    	{
    		exit('hidden, HA!!');
    	}*/
    	
        $this->session->set_flashdata(array('referrer'=>$this->uri->uri_string));
        
        $this->data->article =& $article;
        
        $this->layout->title($article->title, 'News')
       		->set_metadata('description', $this->data->article->intro)
       		->set_metadata('keywords', $this->data->article->category_title.' '.$this->data->article->title)
        
        	->add_breadcrumb('News', 'news');
        
        if($article->category_id > 0)
        {
        	$this->layout->add_breadcrumb($article->category_title, 'news/category/'.$article->category_slug);
        }
        
        $this->layout->add_breadcrumb($article->title, 'news/'.date('Y/m', $article->created_on).'/'.$article->slug);
        $this->layout->create('view', $this->data);
    }
    
    
    // Private methods not used for display
    private function _articles_metadata(&$articles = array())
    {
    	$description = array();
    	// Loop through articles and use titles for meta description
        foreach($this->data->news as &$article)
        {
        	$description[] = $article->title; 
        }
        
        return implode(', ', $description);
    }

}

?>