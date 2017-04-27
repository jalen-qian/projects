<?php
namespace MyApp\Library;

/**
 * 分页
 * @author zhaojianhui
 *
 */
class Paginator
{
    private $total; //总记录
    private $pagesize; //每页显示多少条
    private $page; //当前页码
    private $pagenum; //总页码
    private $url; //地址

    //构造方法初始化
    public function __construct($total, $pagesize)
    {
        $this->total    = $total ? $total : 1;
        $this->pagesize = $pagesize;
        $this->pagenum  = ceil($this->total / $this->pagesize);
        $this->page     = $this->setPage();
        $this->url      = $this->setUrl();
    }

    public function __get($_key)
    {
        return $this->$_key;
    }

    //获取当前页码
    private function setPage()
    {
        if (!empty($_GET['page'])) {
            if ($_GET['page'] > 0) {
                if ($_GET['page'] > $this->pagenum) {
                    return $this->pagenum;
                } else {
                    return $_GET['page'];
                }
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    }

    //获取地址
    private function setUrl()
    {
        $url = $_SERVER["REQUEST_URI"];
        $_par = parse_url($url);
        if (isset($_par['query'])) {
            parse_str($_par['query'], $_query);
            unset($_query['page']);
            if(empty($_query)){
                $url = $_par['path'] . '?' . http_build_query($_query);
            }else{
                $url = $_par['path'] . '?' . http_build_query($_query).'&';
            }
        }else{
            $url = $_par['path'] . '?';
        }
        return $url;
    }
    /**
     * 数字目录
     */
    private function pageList()
    {
        $page = $this->page;
        $total = $this->pagenum;
        $_pagelist = '';
        if($total <= 10){
            for ($i = 1;$i <= $total;$i++){
                if($i == $page){
                    $_pagelist .= '<li class="active"><a href="">' . $page . '</a></li>';
                }else{
                    $_pagelist .= '<li><a href="' . $this->url . 'page=' . $i . '">' . $i . '</a></li>';
                }
            }
        }elseif($page-1 > 5 && $total-$page > 4 ){
            for ($i = $page-5;$i <= $page+4 ;$i++){
                if($i == $page){
                    $_pagelist .= '<li class="active"><a href="">' . $page . '</a></li>';
                }else{
                    $_pagelist .= '<li><a href="' . $this->url . 'page=' . $i . '">' . $i . '</a></li>';
                }
            }
        }elseif ($page-1 <= 5){
            for ($i = 1;$i <= 10 ;$i++){
                if($i == $page){
                    $_pagelist .= '<li class="active"><a href="">' . $page . '</a></li>';
                }else{
                    $_pagelist .= '<li><a href="' . $this->url . 'page=' . $i . '">' . $i . '</a></li>';
                }
            }
        }else{
            for ($i = $total-9;$i <= $total ;$i++){
                if($i == $page){
                    $_pagelist .= '<li class="active"><a href="">' . $page . '</a></li>';
                }else{
                    $_pagelist .= '<li><a href="' . $this->url . 'page=' . $i . '">' . $i . '</a></li>';
                }
            }
        }
        return $_pagelist;
    }

    //首页和上一页
    private function first()
    {
        $page = $this->page;
        $_pagelist = '';
        if($page > 1){
            $prePage = $page -1;
            $_pagelist .= '<li class="previous"><a href="' . $this->url . 'page=1"><i class="icon icon-double-angle-left"></i></a></li>';
            $_pagelist .= '<li class="previous"><a href="' . $this->url . 'page='. $prePage .'">上一页</a></li>';
        }
        return $_pagelist;
    }

    //下一页和尾页
    private function last()
    {
        $page = $this->page;
        $total = $this->pagenum;
        $nextPage = $page+1;
        $_pagelist = '';
        if($page < $total){
            $_pagelist .= '<li class="next"><a href="' . $this->url . 'page='. $nextPage .'">下一页</a></li>';
            $_pagelist .= '<li class="previous"><a href="' . $this->url . 'page='.$total.'"><i class="icon icon-double-angle-right"></i></a></li>';
        }
        return $_pagelist;
    }

    //分页信息
    public function showpage()
    {
        $_page = '<ul class="pager">';
        $_page .= $this->first();
        $_page .= $this->pageList();
        $_page .= $this->last();
        $_page .= '</ul>';
        return $_page;
    }
}
