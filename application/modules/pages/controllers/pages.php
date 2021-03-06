<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pages extends Public_Controller {

    function __construct() 
    {
        parent::Public_Controller();
        
        $this->load->model('pages_m');
    }

    function _remap()
    {
    	// This basically keeps links to /home always pointing to the actual homepage even when the default_controller is changed
		@include(APPPATH.'/config/routes.php'); // simple hack to get the default_controller, could find another way.
		
		$slug = $this->uri->segment(1, NULL);

		// The default route is set to a different module than pages. Send them to there if they come looking for the homepage
		if(!empty($route) && $slug == 'home' && $route['default_controller'] != 'pages')
		{
			redirect('');
		}
		
		// Default route = pages
		else
		{
			// Show the requested page with all segments available
			call_user_func_array(array($this, 'page'), $this->uri->segment_array());
		}
    }
    
    
    function page($slug = 'home')
    {
    	// No data, and its not the home page
        if(!$page = $this->cache->model('pages_m', 'getBySlug', array($slug, DEFAULT_LANGUAGE)) )
        {
        	show_404();
        }
        
        // Parse any settings, links r ==or url tags
        //$this->load->library('data_parser');
        //$this->data->page->body = $this->data_parser->parse($this->data->page->body);
        
        // Not got a meta title? Use slogan for homepage or the normal page title for other pages
        if($page->meta_title == '')
        {
        	$page->meta_title = $slug == 'home' ? $this->settings->item('site_slogan') : $page->title;
        }
        
        // Define data elements
        $this->data->page =& $page;
        
        // Create page output
	    $this->layout->title( $page->meta_title )
	    
        	->set_metadata('keywords', $page->meta_keywords)
        	->set_metadata('description', $page->meta_description)
        	
        	->create('index', $this->data);
    }
    
}

?>