<?php
/**
*	来自：信呼开发团队
*	作者：磐石(rainrock)
*	网址：http://www.rockoa.com/
*	系统文件
*/
class upgradeClassAction extends Action { public function initAction() { $this->keyss = $this->option->getval('systemnum'); if(isempt($this->keyss)){ $rnd = md5(str_shuffle('abcedfghijk').rand(1000,9999)); $this->option->setval('systemnum', $rnd); $this->keyss = $rnd; } } public function dataAjax() { $xinhu = c('xinhu'); $db = m('chargems'); $wet = $xinhu->getwebsite(); $barr = $xinhu->getdata('mode',array('sysnum'=>$this->keyss,'bsste'=>$this->option->getval('auther_aukey'))); if($barr['code']!=200)exit($barr['msg']); $rows = array(); $wodekey= $this->jm->getRandkey(); foreach($barr['data'] as $k=>$rs){ $id = $rs['id']; $state = 0; $key = ''; $ors = $db->getone("`type`=0 and `mid`='$id'"); if($ors){ $state = 1; if($rs['updatedt']>$ors['updatedt'])$state=2; $key= $ors['key']; } $view = '<a href="'.$wet.'view_'.$rs['num'].'.html" target="_blank" class="a">介绍</a>'; if($state==0 && $rs['price']>1)$view.=',<a href="'.$wet.'view_'.$rs['num'].'.html" target="_blank" style="color:red">去购买</a>'; $rows[] = array( 'id' => $id, 'name' => $rs['name'], 'price' => $rs['price'], 'isaz' => $rs['isaz'], 'explain' => $rs['explain'], 'updatedt' => $rs['updatedt'], 'key' => $key, 'view' => $view, 'opt' => $state ); if($k==0){ $wodekey = $rs['wodekey']; if(arrvalue($rs,'sdxe')=='1')$this->option->update('`value`=null','pid=-101'); } } $arr['rows'] = $rows; $arr['wodekey'] = $wodekey; $this->returnjson($arr); } public function shengjianAjax() { $id = $this->post('id'); $key = $this->post('key'); if(!isempt($key) && $this->rock->isjm($key))$key = $this->jm->uncrypt($key); $barr = c('xinhu')->getdata('getinstallfile',array('id'=>$id,'sysnum'=>$this->keyss,'key'=>$key)); if($barr['code']!=200)showreturn('',$barr['msg'],201); $data = $barr['data']; $mrs = $data['rs']; $rows = $data['rows']; $uparr = array(); $huira = $this->gethuiarr($id); $morrs = $this->getmodeuprs($rows); foreach($rows as $k=>$rs){ $file = $rs['filepath']; $bo = true; if(file_exists($file)){ $_size = filesize($file); if($_size == $rs['filesize'])$bo = false; } if($rs['isup']==1)$bo = true; if(isset($huira[$rs['id']]))$bo = false; if(in_array($file, $morrs))$bo = false; if($bo){ $uparr[] = $rs; } } if(!$uparr){ $this->upsueecc($id, $mrs['updatedt'], $key); } showreturn($uparr); } private function upsueecc($id, $updatedt, $key) { $where = "`type`=0 and `mid`='$id'"; $db = m('chargems'); if($db->rows($where)==0)$where=''; $db->record(array( 'type' => 0, 'mid' => $id, 'modeid' => $id, 'updatedt' => $updatedt, 'optdt' => $this->now, 'key' => $this->jm->encrypt($key) ),$where); } private function upsueeccmids($modeid, $mid, $updatedt, $type=1) { $where = "`type`='$type' and `mid`='$mid'"; $db = m('chargems'); if($db->rows($where)==0)$where=''; $db->record(array( 'type' => $type, 'mid' => $mid, 'modeid' => $modeid, 'updatedt' => $updatedt, 'optdt' => $this->now, ),$where); } private function getmodeuprs($frows) { $rows = m('flow_set')->getall('1=1', '`num`,`status`,`isup`'); $hurs = array(); $nomok= array(); $huwj = getconfig('noupgrademode'); if($huwj)$nomok = explode(',', $huwj); foreach($rows as $k=>$rs){ $num = $rs['num']; if($rs['isup']=='0'){ $nomok[]= $num; } } foreach($nomok as $num){ $hurs[] = ''.P.'/flow/input/inputjs/mode_'.$num.'.js'; $hurs[] = ''.P.'/flow/input/mode_'.$num.'Action.php'; $hurs[] = ''.P.'/flow/page/input_'.$num.'.html'; $hurs[] = ''.P.'/flow/page/view_'.$num.'_0.html'; $hurs[] = ''.P.'/flow/page/view_'.$num.'_1.html'; $hurs[] = ''.P.'/flow/page/view_'.$num.'_2.html'; $hurs[] = ''.P.'/flow/page/viewpage_'.$num.'.html'; $hurs[] = ''.P.'/flow/page/viewpage_'.$num.'_0.html'; $hurs[] = ''.P.'/flow/page/viewpage_'.$num.'_1.html'; $hurs[] = ''.P.'/flow/page/rock_page_'.$num.'.php'; $hurs[] = ''.P.'/model/flow/'.$num.'Model.php'; } return $hurs; } public function shengjianssAjax() { $mid = (int)$this->post('id'); $fileid = (int)$this->post('fileid'); $key = $this->post('key'); if(!isempt($key) && $this->rock->isjm($key))$key = $this->jm->uncrypt($key); $oi = $this->post('oii'); $len = $this->post('lens'); $updatedt = $this->post('updatedt'); $upbo = true; $ors = m('chargems')->getone("`type`=1 and `mid`='$fileid'"); if($ors && $updatedt<=$ors['optdt'])$upbo = false; if(isempt($updatedt))$upbo = true; if($upbo){ $barr = c('xinhu')->getdata('getinstallfileid',array('fid'=>$fileid,'sysnum'=>$this->keyss,'key'=>$key,'ban'=>$this->post('ban'))); if($barr['code'] != 200)exit($barr['msg']); $data = $barr['data']; $type = $data['type']; $filepath = $data['filepath']; $fcont = $this->jm->base64decode($data['content']); if($fcont != ''){ if($type==1){ $bmsg = m('beifen')->updatefabric($fcont); if($bmsg != 'ok')exit($bmsg); $this->upsueeccmids($mid, $fileid, $updatedt, 1); } if($type==9){ $this->rock->createdir($filepath); $this->beifenfile($filepath); @$bo = file_put_contents($filepath, $fcont); if(!$bo)exit('无法写入：'.$filepath.''); } if($type==10){ $this->rock->createdir($filepath); @file_put_contents($filepath, $fcont); $sqarr = explode('ROCKSPLIT', $fcont); $lastid= '0'; foreach($sqarr as $_sql1)if($_sql1){ if($_sql1=='LASTID'){ $lastid = $this->db->insert_id(); if(isempt($lastid))$lastid = '0'; }else{ if(contain($_sql1,'{lastid}')){ if($lastid!='0')$this->db->query(str_replace('{lastid}',$lastid,$_sql1),false); }else{ $this->db->query($_sql1,false); } } } } } $udt = $data['updatedt']; }else{ $udt = $this->now; } if($oi+1 == $len){ $this->upsueecc($mid, $udt, $key); } echo 'ok'; } private function beifenfile($path) { if(!file_exists($path))return; $wz = strripos($path, '/'); $dir = ''; if($wz===false){ $file = $path; }else{ $dir = substr($path, 0, $wz); $file = substr($path, $wz+1); } $dir = str_replace('/','-', $dir); $nfile = ''.$dir.''.date('YmdHis').'@'.$file.''; $topath = ''.UPDIR.'/logs/'.date('Y-m').'/'.$nfile.''; $this->rock->createdir($topath); @copy($path, $topath); } private function shengjifile($frs, $key, $modeid) { $fid = $frs['id']; $where = "`type`=1 and `mid`='$fid'"; $db = m('chargems'); $ors = $db->getone($where); if($ors){ if($frs['updatedt']<=$ors['optdt'])return; }else{ $where = ''; } $barr = c('xinhu')->getdata('getfileinstall',array('id'=>$fid,'sysnum'=>$this->keyss,'key'=>$key)); if($barr['code']!=200)exit($barr['msg']); $fcont = $this->jm->base64decode($barr['data']); if(isempt($fcont))return; $mkdir = ''.UPDIR.'/'.date('Y-m'); if($frs['type']==0){ if(!is_dir($mkdir))mkdir($mkdir); $filemy = $mkdir.'/install'.time().rand(1000,9999).'.zip'; file_put_contents($filemy, $fcont); $bmsg = c('zip')->unzip($filemy,'./'); unlink($filemy); if($bmsg != 'ok')exit($bmsg); } if($frs['type']==1){ $bmsg = m('beifen')->updatefabric($fcont); if($bmsg != 'ok')exit($bmsg); } $db->record(array( 'type' => 1, 'mid' => $fid, 'modeid' => $modeid, 'updatedt' => $frs['updatedt'], 'optdt' => $this->now, ),$where); } public function tontbudataAjax() { $lx = (int)$this->get('lx'); $snum = $this->get('snum'); $barr = c('xinhu')->getdata('getaneydata', array('lx'=>$lx,'snum'=>$snum,'ban'=>$this->post('ban'))); if($barr['code']!=200)exit($barr['msg']); $data = $barr['data']; if(URL=='http://127.0.0.1/app/xinhu/')exit('本地测试完成'); $msgr = ''; if($lx==0)$this->tonbbumenu($data['menu']); if($lx==1)$msgr=$this->tonbbumode($data['mode']); if($lx==4)$this->tonbbumodewq($data['mode']); if($lx==2)$this->tonbbuying($data['yydata']); if($lx==3)$this->tonbbutask($data['task']); if($lx==5)$msgr=$this->tonbbumode($data['mode'],$snum); echo '同步完成'.$msgr.''; } private function tonbbumenu($data) { $db = m('menu'); $caid = '0'; foreach($data as $k=>$rs){ $id = $rs['id']; if($db->rows('id='.$id.'')>0){ unset($rs['status']); unset($rs['ispir']); unset($rs['ishs']); if(isempt($rs['num']))unset($rs['num']); if(isempt($rs['color']))unset($rs['color']); if(isempt($rs['icons']))unset($rs['icons']); $db->update($rs, 'id='.$id.''); }else{ $db->insert($rs); } $caid.=','.$id.''; } $db->update('status=0','id not in('.$caid.')'); } private function tonbbumenuss($data, $pid,$npid, $db){ foreach($data as $k=>$rs){ if($rs['pid']==$pid){ $name = $rs['name']; $id = $rs['id']; $where1 = " and `name`='$name'"; if(!isempt($rs['url']))$where1 = " and (`name`='$name' or `url`='".$rs['url']."')"; $where = "`pid`='$npid' $where1"; $yid = (int)$db->getmou('id', $where); unset($rs['id']); $rs['optdt'] = $this->rock->now; if($yid==0){ $rs['pid']= $npid; $this->addci++; $yid= $db->insert($rs); }else{ $this->eddci++; unset($rs['status']); unset($rs['ispir']); unset($rs['ishs']); if(isempt($rs['num']))unset($rs['num']); if(isempt($rs['color']))unset($rs['color']); if(isempt($rs['icons']))unset($rs['icons']); $db->update($rs, $yid); } $npid1 = $yid; $this->tonbbumenuss($data, $id, $npid1, $db); } } } private function tonbbumode($data, $snum='') { $zcis = 0; $db = m('flow_set'); $db1 = m('flow_element'); $db2 = m('flow_menu'); $db3 = m('flow_extent'); $db5 = m('flow_course'); $db6 = m('flow_where'); $db7 = m('flow_todo'); $iszdbo = false; if(!isempt($snum))$iszdbo= true; foreach($data as $num=>$arr){ if($iszdbo && $num!=$snum)continue; $moders = $db->getone("`num`='$num'",'`id`,`isup`,`name`'); $modeid = 0; $isup = 1; if($moders){ $modeid = (int)$moders['id']; $isup = (int)$moders['isup']; if($isup==0){ if($num==$snum)exit('此['.$snum.'.'.$moders['name'].']模块未开启同步更新'); continue; } } $flow_set = $arr['flow_set']; if(isset($flow_set['id']))unset($flow_set['id']); $isadd = false; if($modeid==0){ $modeid = $db->insert($flow_set); $isadd = true; }else{ if($iszdbo){ unset($flow_set['name']); unset($flow_set['pctx']); unset($flow_set['mctx']); unset($flow_set['wxtx']); unset($flow_set['emtx']); unset($flow_set['receid']); unset($flow_set['recename']); unset($flow_set['status']); $db->update($flow_set, $modeid); }else{ $db->update(array( 'where' => $flow_set['where'], 'sort' => $flow_set['sort'], 'type' => $flow_set['type'], 'summary' => $flow_set['summary'], 'summarx' => $flow_set['summarx'], 'tables' => $flow_set['tables'], 'names' => $flow_set['names'], 'isscl' => $flow_set['isscl'], 'statusstr' => $flow_set['statusstr'] ), $modeid); } } $flow_where = $arr['flow_where']; $sid6 = '0'; foreach($flow_where as $k6=>$rs6){ $rs6['setid'] = $modeid; if(isset($rs6['id']))unset($rs6['id']); $num = $rs6['num']; if(isempt($num))continue; $where = "`setid`='$modeid' and `num`='$num'"; if($db6->rows($where)==0){ $db6->insert($rs6); }else{ $db6->update($rs6, $where); } } $flow_element= $arr['flow_element']; $sid1s = '0'; foreach($flow_element as $k1=>$rs1){ $rs1['mid'] = $modeid; if(isset($rs1['id']))unset($rs1['id']); $where = "`mid`='$modeid' and `fields`='".$rs1['fields']."' and `iszb`='".$rs1['iszb']."'"; if($db1->rows($where)==0){ $sid1 = $db1->insert($rs1); }else{ unset($rs1['name']); $db1->update($rs1, $where); $sid1 = $db1->getmou('id', $where); } $sid1s.=','.$sid1.''; } if($iszdbo){ $db1->delete("`mid`='$modeid' and `id` not in($sid1s)"); } $flow_extent= $arr['flow_extent']; foreach($flow_extent as $k3=>$rs3){ $rs3['modeid'] = $modeid; $sid = $rs3['id']; if($db3->rows('id='.$sid.'')>0){ $db3->update($rs3, 'id='.$sid.''); }else{ $db3->insert($rs3); } } $flow_menu= $arr['flow_menu']; if($flow_menu){ $sids = '0'; foreach($flow_menu as $k2=>$rs2){ $rs2['setid'] = $modeid; $sid = $rs2['id']; if($db2->rows('id='.$sid.'')>0){ $sids.=','.$sid.''; $db2->update($rs2, 'id='.$sid.''); }else{ $db2->insert($rs2); $sids.=','.$this->db->insert_id().''; } } $db2->delete("`setid`='$modeid' and `id` not in($sids)"); } if(isset($arr['flow_course'])){ if($db5->rows('setid='.$modeid.'')==0){ $flow_course = $arr['flow_course']; foreach($flow_course as $k5=>$rs5){ if(isset($rs5['id']))unset($rs5['id']); $rs5['setid'] = $modeid; if(isset($rs5['children']))unset($rs5['children']); $db5->insert($rs5); } } } if(isset($arr['flow_todo'])){ $flow_todo = $arr['flow_todo']; $sids7 = '0'; foreach($flow_todo as $k7=>$rs7){ if(isset($rs7['id']))unset($rs7['id']); $rs7['setid'] = $modeid; $where = "`setid`='$modeid' and `name`='".$rs7['name']."'"; if($db7->rows($where)==0){ $sid7 = $db7->insert($rs7); }else{ $db7->update($rs7, $where); $sid7 = $db7->getmou('id', $where); } $sids7.=','.$sid7.''; } } $zcis++; } return '共'.$zcis.'个模块'; } private function tonbbumodewq($data) { $db = m('flow_set'); $this->initstalltable('flow_set'); $this->initstalltable('flow_element'); $this->initstalltable('flow_menu'); $this->initstalltable('flow_extent'); $this->initstalltable('flow_course'); $this->initstalltable('flow_where'); $this->initstalltable('flow_todo'); foreach($data as $num=>$arr){ $flow_set = $arr['flow_set']; $flow_element = $arr['flow_element']; $flow_menu = $arr['flow_menu']; $flow_extent = $arr['flow_extent']; $flow_course = $arr['flow_course']; $flow_where = $arr['flow_where']; $flow_todo = $arr['flow_todo']; $db->insert($flow_set); $this->insertdata($flow_element, 'flow_element'); $this->insertdata($flow_menu, 'flow_menu'); $this->insertdata($flow_extent, 'flow_extent'); $this->insertdata($flow_course, 'flow_course'); $this->insertdata($flow_where, 'flow_where'); $this->insertdata($flow_todo, 'flow_todo'); } } private function initstalltable($table) { $sql1 = "delete from `[Q]".$table."`"; $sql2 = "alter table `[Q]".$table."` AUTO_INCREMENT=1"; $this->db->query($sql1, false); $this->db->query($sql2, false); } private function insertdata($data, $table) { $db = m($table); if($data)foreach($data as $k=>$rs){ $db->insert($rs); } } private function tonbbuying($data) { $db = m('im_group'); $dbs = m('im_menu'); foreach($data as $k=>$yydata){ $rs = $yydata['data']; $menu = $yydata['menu']; $name = $rs['name']; unset($rs['id']); $where = "`name`='$name' and `type`=2"; if(!isempt($rs['num']))$where = "`num`='".$rs['num']."' and `type`=2"; if($db->rows($where)==0){ $mid = $db->insert($rs); $this->addyymenu($menu, $dbs, $mid, 0); }else{ $mid = (int)$db->getmou('id', $where); $db->update(array( 'face' => $rs['face'], 'url' => $rs['url'], 'types' => $rs['types'], 'sort' => $rs['sort'], 'urlpc' => $rs['urlpc'], 'urlm' => $rs['urlm'], 'yylx' => $rs['yylx'], 'name' => $rs['name'], 'iconfont' => $rs['iconfont'], 'iconcolor' => $rs['iconcolor'], 'explain' => $rs['explain'], ),$where); $this->addyymenu($menu, $dbs, $mid, 0); } } } private function addyymenu($menu, $dbs, $mid, $pid) { $ssid = '0'; foreach($menu as $k1=>$rs1){ unset($rs1['id']); $menusub = false; if(isset($rs1['menusub'])){ $menusub = $rs1['menusub']; unset($rs1['menusub']); } $rs1['mid'] = $mid; $rs1['pid'] = $pid; $where = "`name`='".$rs1['name']."' and `pid`='$pid' and `mid`='$mid'"; $sid = (int)$dbs->getmou('id', $where); if($sid == 0){ $where = ''; } $dbs->record($rs1, $where); if($sid==0)$sid = $this->db->insert_id(); if($menusub)$this->addyymenu($menusub, $dbs, $mid, $sid); $ssid .= ','.$sid.''; } $dbs->delete("pid='$pid' and `mid`='$mid' and `id` not in($ssid)"); } private function tonbbutask($data) { $db = m('task'); foreach($data as $k=>$rs){ $where = "`url`='".$rs['url']."'"; $sid = (int)$db->getmou('id', $where); if($sid == 0){ $where = ''; }else{ unset($rs['todoid']); unset($rs['todoname']); } unset($rs['id']); unset($rs['state']); unset($rs['lastdt']); $db->record($rs, $where); } } public function delmodelAjax() { $id = (int)$this->post('id'); m('chargems')->delete("`modeid`='$id' and `type` in(0,1) and `modeid`>0"); $this->showreturn(''); } public function datadubiAjax() { $id = (int)$this->get('id'); $barr = c('xinhu')->getdata('getinstallfile',array('id'=>$id,'sysnum'=>$this->keyss)); if($barr['code']!=200)showreturn('',$barr['msg'],201); $data = $barr['data']; $mrs = $data['rs']; $rows = $data['rows']; $uparr = array(); $morrs = $this->getmodeuprs($rows); foreach($rows as $k=>$rs){ $file = $rs['filepath']; $bo = true; $zt = 'add'; if(file_exists($file)){ $_size = filesize($file); if($_size == $rs['filesize'])$bo = false; $zt = 'edit'; } if($rs['type']==1)$zt = ''; if($rs['isup']==1)$bo = true; $rs['ting'] = 0; if(in_array($file, $morrs))$rs['ting'] = 1; if($bo){ $rs['zt']= $zt; $uparr[] = $rs; } } $huira = $this->gethuiarr($id); $sarr1 = $sarr2 = array(); $strh = ''; foreach($uparr as $k=>$rs1){ $ishui = 0; if(isset($huira[$rs1['id']]))$ishui = 1; if($rs1['ting']==1)$ishui=1; $uparr[$k]['ishui'] = $ishui; if($ishui==1){ $sarr2[] = $uparr[$k]; }else{ $sarr1[] = $uparr[$k]; } } $this->returnjson(array('strh'=>$strh,'rows'=>array_merge($sarr1, $sarr2))); } private function gethuiarr($id) { $hurs = m('chargems')->getall("`modeid`='$id' and `type`=2",'mid'); $huira = array(); foreach($hurs as $k2=>$rs2)$huira[$rs2['mid']] = 1; return $huira; } public function hullueAjax() { $id = (int)$this->post('id'); $lx = (int)$this->post('lx'); $sid = $this->post('sid'); $db = m('chargems'); $db->delete("`modeid`='$id' and `mid` in($sid) and `type`=2"); $sad = explode(',', $sid); if($lx==0)foreach($sad as $sids){ $db->insert(array( 'optdt' => $this->now, 'type' => 2, 'mid' => $sids, 'modeid' => $id )); } echo 'ok'; } public function otherdataAjax() { $rows = array(); $barr = c('xinhuapi')->getdata('other','data', array( 'page' => (int)$this->get('page','1'), 'limit' => (int)$this->get('limit','15'), )); if(!$barr['success'])return $barr['msg']; $data = $barr['data']; $rows = $data['rows']; if($rows){ $anzrr = m('chargems')->getall('`type`=3'); $anzra = array(); foreach($anzrr as $k=>$rs)$anzra[$rs['modeid']] = $rs['updatedt']; foreach($rows as $k=>$rs){ $anzt = 0; if(isset($anzra[$rs['id']])){ $dtsd= $anzra[$rs['id']]; if($rs['updatedt']>$dtsd){ $anzt = 2; }else{ $anzt = 1; } } $rows[$k]['anzt'] = $anzt; } $data['rows'] = $rows; } return $data; } public function otherinstallAjax() { $id = (int)$this->get('id','0'); if(getconfig('systype')=='demo')return returnerror('演示不要操作'); $barr = c('xinhuapi')->getdata('other','instcheck', array( 'id' => $id, )); if(!$barr['success'])return $barr; $da = $barr['data']; return returnsuccess(array( 'msg' => '安装完成', 'name' => $da['name'], 'path' => ''.$id.'', )); } public function loadinstallinfoAjax() { if(getconfig('systype')=='demo')return returnerror('演示不要操作'); if($this->adminid!=1)return returnerror('不是管理员不要操作'); $nwsp = $this->get('path'); if(!$nwsp){ $nwsp = ''; }else{ $nwsp = $this->jm->base64decode($nwsp); } if(isempt($nwsp))return returnerror('无效安装'); if(is_numeric($nwsp)){ $barr = c('xinhuapi')->getdata('other','instinfo', array( 'id' => $nwsp, )); if(!$barr['success'])return $barr; $farr = $barr['data']['farr']; $filesize = $barr['data']['filesize']; }else{ $farr = c('zip')->zipget($nwsp); if(!is_array($farr))return returnerror($farr); $filesize = filesize($nwsp); } $path = ''.UPDIR.'/logs/'.md5($nwsp).''; $filestr = ''; $agentstr = ''; $tablestr = ''; $menustr = ''; foreach($farr as $k=>$rs){ $_pluj = $rs['filepath']; $spath = $path.'/'.$_pluj; $conts = $rs['filecontent']; if(!contain($_pluj,'installconfig')){ $filestr.=''.$_pluj.'<br>'; $fileext = substr($_pluj,-3); if($fileext=='jpg' || $fileext=='png' || $fileext=='gif'){ if($conts)$this->rock->createtxt($spath, base64_decode($conts)); } }else{ $this->rock->createtxt($spath, base64_decode($conts)); } } $confpath = $path.'/installconfig/xinhuoa_config.php'; if(!file_exists($confpath))return '无效安装包'.$confpath.''; $conf = require($confpath); $modepath = $path.'/installconfig/xinhuoa_data.json'; $mysqlpath = $path.'/installconfig/xinhuoa_mysql.json'; $modestr = ''; $menuarr = array(); if(file_exists($modepath)){ $dsta = json_decode(file_get_contents($modepath), true); if(isset($dsta['mode']))foreach($dsta['mode'] as $bh=>$info){ $modestr.=''.$info['flow_set']['name'].'('.$bh.') '; } if(isset($dsta['menu']))foreach($dsta['menu'] as $cd=>$cdrs){ if($cd>0)$menustr.='<div style="margin:5px 0px" class="blank1"></div>'; $menustr.='<input class="btn btn-default btn-xs" click="xuancaid,'.$cdrs['id'].'" type="button" value="选上级菜单"><br>'.$cdrs['name'].'('.$cdrs['url'].')'; $menuarr[$cdrs['id']] = '-1'; if(isset($cdrs['children']))foreach($cdrs['children'] as $cd1=>$cdrs1){ $menustr.='<br>&nbsp;┣'.$cdrs1['name'].'('.$cdrs1['url'].') '; if(isset($cdrs1['children']))foreach($cdrs1['children'] as $cd2=>$cdrs2){ $menustr.='<br>&nbsp;&nbsp;┣'.$cdrs2['name'].'('.$cdrs2['url'].') '; } } } if(isset($dsta['yydata']))foreach($dsta['yydata'] as $yb=>$ybrs){ $agentstr.='<img src="'.$path.'/'.$ybrs['data']['face'].'" align="absmiddle" width="20px" height="20px">'.$ybrs['data']['name'].' '; } } if(file_exists($mysqlpath)){ $dstd = json_decode(file_get_contents($mysqlpath), true); foreach($dstd as $dbs=>$nse){ $tablestr.=','.$dbs.''; } } if($tablestr)$tablestr = substr($tablestr,1); $conf['modestr'] = $modestr; $conf['filestr'] = $filestr; $conf['tablestr'] = $tablestr; $conf['menustr'] = $menustr; $conf['agentstr'] = $agentstr; $conf['menuarr'] = $menuarr; $conf['pathstr'] = $this->jm->base64encode($nwsp); $conf['filesizecn'] = $this->rock->formatsize($filesize); return returnsuccess($conf); } public function getmenuAjax() { $glx = (int)$this->get('glx','0'); if($glx==0)$arr[] = array('name'=>'顶级','value'=>'0'); $db = m('menu'); $rows = $db->getall('`pid`=0 and `status`=1','id,name','sort'); foreach($rows as $k=>$rs){ $arr[] = array('name'=>$rs['name'],'value'=>$rs['id'],'subname'=>$rs['id']); $rows1 = $db->getall('`pid`='.$rs['id'].' and `status`=1','id,name','sort'); foreach($rows1 as $k1=>$rs1){ $arr[] = array('name'=>$rs1['name'],'value'=>$rs1['id'],'padding'=>24,'subname'=>$rs1['id']); if($glx==1){ $rows2 = $db->getall('`pid`='.$rs1['id'].' and `status`=1','id,name','sort'); foreach($rows2 as $k2=>$rs2){ $arr[] = array('name'=>$rs2['name'],'value'=>$rs2['id'],'padding'=>48,'subname'=>$rs2['id']); } } } } return $arr; } public function getyydataAjax() { $arr = array(); $db = m('im_group'); $rows = $db->getall('`type`=2 and `valid`=1','id,name,types,face','sort'); foreach($rows as $k=>$rs){ $arr[] = array('name'=>$rs['name'],'value'=>$rs['id'],'iconsimg'=>$rs['face'],'subname'=>$rs['types']); } return $arr; } public function newinstallinfoAjax() { if(getconfig('systype')=='demo')return '演示不要操作'; if($this->adminid!=1)return '不是管理员不要操作'; $nwsp = $this->jm->base64decode($this->get('path')); if(is_numeric($nwsp)){ $barr = c('xinhuapi')->getdata('other','instfile', array( 'id' => $nwsp, )); if(!$barr['success'])return $barr; $farr = $barr['data']['farr']; }else{ $farr = c('zip')->zipget($nwsp); if(!is_array($farr))return $farr; } $path = ''.UPDIR.'/logs/'.md5($nwsp).''; $confpath = $path.'/installconfig/xinhuoa_config.php'; if(!file_exists($confpath))return '无效安装包'; $conf = require($confpath); $modepath = $path.'/installconfig/xinhuoa_data.json'; $mysqlpath = $path.'/installconfig/xinhuoa_mysql.json'; if(file_exists($mysqlpath)){ $bmsg = m('beifen')->updatefabric(file_get_contents($mysqlpath), 1); if($bmsg!='ok')return $bmsg; } if(file_exists($modepath)){ $dsta = json_decode(file_get_contents($modepath), true); if(isset($dsta['menu']))foreach($dsta['menu'] as $cd=>$cdrs){ $pid = $this->get('menupid'.$cdrs['id'].'','-1'); if($pid!='-1'){ $where = "`name`='".$cdrs['name']."'"; if(!isempt($cdrs['url']))$where.=" and `url`='".$cdrs['url']."'"; if(!isempt($cdrs['num']))$where.=" and `num`='".$cdrs['num']."'"; if(m('menu')->rows($where)==0)$this->saveshangiji('menu',$cdrs, $pid,'pid'); } } if(isset($dsta['yydata']))$this->tonbbuying($dsta['yydata']); if(isset($dsta['mode']))$this->tonbbumode($dsta['mode']); } foreach($farr as $k=>$rs){ $_pluj = $rs['filepath']; $spath = $path.'/'.$_pluj; if(!contain($_pluj,'installconfig')){ $bo = $this->rock->createtxt($_pluj, base64_decode($rs['filecontent'])); if(!$bo)return '无法写入:'.$_pluj.''; } if(file_exists($spath))unlink($spath); } if(is_numeric($nwsp)){ $this->upsueeccmids($nwsp, $nwsp, $this->now, 3); }else{ @unlink($nwsp); } m('log')->addlog('模块插件安装','名称:'.$conf['name'].',作者:'.$conf['zuozhe'].',版本:'.$conf['ver'].''); return 'ok'; } private function saveshangiji($tab,$da,$pid,$pfid) { unset($da['id']); $da[$pfid] = $pid; $children = false; if(isset($da['children'])){ $children =$da['children']; unset($da['children']); } $nid = m($tab)->insert($da); if($nid && $children)foreach($children as $k=>$rs){ $this->saveshangiji($tab,$rs, $nid,$pfid); } } public function delotherAjax() { $id = (int)$this->post('id'); m('chargems')->delete("`modeid`='$id' and `type`=3"); $this->showreturn(''); } }