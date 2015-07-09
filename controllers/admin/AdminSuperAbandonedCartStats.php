<?php
require_once(dirname(__FILE__) . '/../../classes/Campaign.php');
require_once(dirname(__FILE__) . '/../../classes/CampaignHistory.php');

class AdminSuperAbandonedCartStatsController extends AdminController
{

    public $messageHeader;

    public function __construct()
    {
      
        $this->table = 'campaign';
        $this->className = 'Campaign';
        $this->module = 'superabandonedcart';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->_defaultOrderBy = 'created';
        $this->_defaultorderWay = 'DESC';
        $this->bootstrap = true;
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );
        
        if (Shop::isFeatureActive()) {
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        }
        
        parent::__construct();
        
        $this->fields_list = array(
            'id_campaign' => array(
                'title' => $this->l('Campaign #'),
                'width' => 10,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
             'name' => array(
                'title' => $this->l('Name'),
                'width' => 100,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false,
             ),
             'execution_time_day' => array(
                'title' => $this->l('Execution schedule day'),
                'width' => 10,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'callback' => 'execution_time_day'
             ),
             'execution_time_hour' => array(
                'title' => $this->l('Execution schedule hour'),
                'width' => 10,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'callback' => 'execution_time_hour'
             ),
             'active' => array(
                'title' => $this->l('Status'),
                'width' => '70',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter' => false,
                'search' => false
             )
        );
        $this->_defaultOrderBy = 'a.id_campaign';
        $this->_defaultOrderWay = 'DESC';
        
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->_group = 'GROUP BY a.id_campaign';
        }
        
        parent::__construct();
        
    }
    
    public function renderList()
    {
        
        return  $this->renderHistoryList();
       
    }
    
    public function initToolbar()
    {
        
        parent::initToolbar();
    
    }
    

    public function renderHistoryList()
    {
        

        $sql = 'SELECT  SUM(click) AS click, SUM(converted)  AS converted, COUNT(`id_customer`) AS total_camp_sent , id_campaign FROM '._DB_PREFIX_.'campaign_history GROUP BY id_campaign';
        $res = Db::getInstance()->ExecuteS($sql);
        //d($res);
        $i=0;
        $label = $data = $converted = $total_camp_sent = '';
        $t = count($res);
        $convertedPie = '';
        
        $pieTpl = '
		<div class="col-lg-3 clearfix">
		<h5>%s</h5>
		<canvas id="convertedCart_%d" width="200" height="200"></canvas>
		<script>
			var convertedCart_%d = document.getElementById("convertedCart_%d").getContext("2d");
			var data = [
				{
					value: %d,
					color:"#F7464A",
					highlight: "#FF5A5E",		
					label: "%s"
				},
				{
					value: %d,
					color:"#F7464A",
					highlight: "#FF5A5E",		
					label: "%s"
				}
			]
			
			var pie_%d = new Chart(convertedCart_%d).Pie(data,Chart.defaults.Pie);
		</script></div>
		';
        
        foreach ($res as $r) {
            $campaign = new Campaign($r['id_campaign']);
            $label .=  '"'.substr($campaign->name, 0, 18).'.."'. ( $i<$t-1 ? ',' : '' ) ;
            $data .= ''.$r['click'] .''. ( $i<$t-1 ? ',' : '' ) ;
            $converted .= ''.$r['converted'] .''. ( $i<$t-1 ? ',' : '' ) ;
            $converted .= ''.$r['total_camp_sent'] .''. ( $i<$t-1 ? ',' : '' ) ;
            
            $convertedPie .= sprintf($pieTpl, $campaign->name, $r['id_campaign'], $r['id_campaign'], $r['id_campaign'], $r['converted'], $this->l('Converted cart'), ( $r['total_camp_sent'] - $r['converted'] ), $this->l('Total mails sent'), $r['id_campaign'], $r['id_campaign']);
            
            $i++;
            
        }
        
        
        $this->context->smarty->assign(array(
                                            'data' => $data,
                                            'label' => $label,
                                            'convertedPie' => $convertedPie
                                            ));
        

        return $this->context->smarty->fetch(dirname(__FILE__).'/../../stats.tpl');

        return $helper->generateList($historyList, $fields_list);
    }
}
