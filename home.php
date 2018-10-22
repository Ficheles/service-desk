<?php 
header('Content-Type: text/html; charset=iso-8859-1');
 /*                        Copyright 2005 Fl�vio Ribeiro

         This file is part of OCOMON.

         OCOMON is free software; you can redistribute it and/or modify
         it under the terms of the GNU General Public License as published by
         the Free Software Foundation; either version 2 of the License, or
         (at your option) any later version.

         OCOMON is distributed in the hope that it will be useful,
         but WITHOUT ANY WARRANTY; without even the implied warranty of
         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         GNU General Public License for more details.

         You should have received a copy of the GNU General Public License
         along with Foobar; if not, write to the Free Software
         Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  */session_start();

	include ("PATHS.php");
	//include ("".$includesPath."var_sessao.php");
	include ("includes/functions/funcoes.inc");
	include ("includes/javascript/funcoes.js");

	include ("includes/queries/queries.php");
	include ($includesPath."config.inc.php");
	// ("".$includesPath."languages/".LANGUAGE."");
	include ($includesPath."versao.php");

	include("includes/classes/conecta.class.php");
	include("includes/classes/auth.class.php");



	if ($_SESSION['s_logado']==0)
	{
	        print "<script>window.open('index.php','_parent','')</script>";
		exit;
	}


	$conec = new conexao;
	$conect=$conec->conecta('MYSQL');

	$_SESSION['s_page_home'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>OCOMON <?php echo VERSAO; ?></title>

	<link rel='stylesheet' type='text/css' href='includes/css/bootstrap/bootstrap.css'>
	<link rel=stylesheet type='text/css' href='includes/css/estilos.css.php'>
</head>
<body>
<?

	$auth = new auth;
	$auth->testa_user($_SESSION['s_usuario'],$_SESSION['s_nivel'],$_SESSION['s_nivel_desc'],3);

	//Todas as �reas que o usu�rio percente
	$uareas = $_SESSION['s_area'];
	if ($_SESSION['s_uareas']) {
		$uareas.=",".$_SESSION['s_uareas'];
	}

	$qryTotal = "select a.sistema area, a.sis_id area_cod from ocorrencias o left join sistemas a on o.sistema = a.sis_id".
			" left join `status` s on s.stat_id = o.status where o.sistema in (".$uareas.") and s.stat_painel in (1,2) ";
	
	$execTotal = mysql_query($qryTotal) or die (TRANS('MSG_ERR_TOTAL_OCCO'). $qryTotal);
	$regTotal = mysql_num_rows($execTotal);

	//Todas as �reas que o usu�rio percente
	$qryAreas = "select count(*) total, a.sistema area, a.sis_id area_cod from ocorrencias o left join sistemas a on o.sistema = a.sis_id".
			" left join `status` s on s.stat_id = o.status where o.sistema in (".$uareas.") and s.stat_painel in (1,2) ".
			"group by a.sistema";
	$execAreas = mysqli_query($conect,$qryAreas) or die(TRANS('MSG_ERR_RESCUE_ALL_OCCO'). $qryAreas);
	$regAreas = mysqli_num_rows($execAreas);

?>
		
	<br>
	<table border='0' cellpadding='5' cellspacing='0' align='center' width='100%'>
		<tr>
			<td colspan='7'>
				<img ID='imggeral' src='./includes/icons/close.png' width='9' height='9' style="{cursor: pointer;}" onClick="invertView('geral')">&nbsp;
			<b><?php echo TRANS('THEREARE');?><font color='red'><?php echo $regTotal; ?></font>
			<?php echo TRANS('HOME_OPENED_CALLS'); ?></b>
			</td>
		</tr>

		<tr>
			<td style='{padding-left:5px;}'>
				<div id='geral' > <!-- //style='display:none;' -->

<?php
	$a = 0;
	$b = 0;
	while ($rowAreas = mysql_fetch_array($execAreas)) {
?>		

					<table border='0' cellpadding='5' cellspacing='0' align='center' width='100%'>
						<tr>
							<td colspan='7'>
								<IMG ID='imgocorrencias<?php echo $b;?>' SRC='./includes/icons/close.png' width='9' height='9' style="{cursor: pointer;}" onClick="invertView('ocorrencias<?php echo $b;?>')">&nbsp;
								<b><?php echo TRANS('THEREARE');?> 
									<font color='red'><?php echo $rowAreas['total'];?></font><?php echo TRANS('HOME_OPENED_CALLS_TO_AREA');?>: 
									<font color='green'><?php echo $rowAreas['area'];?></font>
								</b>
							</td>
						</tr>
						<tr>
							<td style='{padding-left:5px;}'>
								<div id='ocorrencias<?php echo $b;?>'> <!--//style='display:none;'-->
<?php						

			//TOTAL DE N�VEIS DE STATUS
		$qryStatus = "select count(*) total, o.*, s.* from ocorrencias o left join `status` s on o.status = s.stat_id where ".
				"o.sistema = ".$rowAreas['area_cod']." and s.stat_painel in (1,2) group by s.status";
		$execStatus = mysqli_query($conect,$qryStatus) or die (TRANS('MSG_ERR_QRY_STATUS'). $qryStatus);
		//$a = 0;
		print "<TABLE border='0' cellpadding='5' cellspacing='0' align='center' width='100%'>";
		While ($rowStatus = mysqli_fetch_array($execStatus)) {
			print "<tr><td colspan='7'><IMG ID='imgstatus".$a."' SRC='./includes/icons/open.png' width='9' height='9' ".
				"STYLE=\"{cursor: pointer;}\" onClick=\"invertView('status".$a."')\">&nbsp;<b>".TRANS('OCO_FIELD_STATUS').": ".$rowStatus['status']." - ".
				"".$rowStatus['total']." ocorr�ncias</b><br>";
			print "<div id='status".$a."' style='display:none;' >"; //style='display:none;'

			print "<TABLE border='0' style='{padding-left:10px;}' cellpadding='5' cellspacing='0' align='left' width='100%'>";

			$qryDetail = $QRY["ocorrencias_full_ini"]." WHERE o.sistema = ".$rowAreas['area_cod']." and s.stat_painel in (1,2) and ".
					" o.status = ".$rowStatus['stat_id']."";
			$execDetail = mysqli_query($conect,$qryDetail) or die (TRANS('MSG_ERR_RESCUE_DATA_OCCO') .$qryDetail);

			print "<tr class='header'><td class='line'>".TRANS('COL_NUMBER')."</td><td class='line'>".TRANS('COL_PROB')."</td><td class='line'>".TRANS('OCO_CONTACT')."<br>".TRANS('OCO_PHONE')."</td><td class='line'>".TRANS('OCO_LOCAL')."<br>".TRANS('OCO_DESC')."</td><td class='line'>".TRANS('FIELD_LAST_OPERATOR')."</td></tr>";

			$j=2;
			while ($rowDetail = mysqli_fetch_array($execDetail)){
				if ($j % 2) {
						$trClass = "lin_par";
				}
				else {
						$trClass = "lin_impar";
				}
				$j++;

				print "<tr class=".$trClass." id='linha".$j."".$a."' onMouseOver=\"destaca('linha".$j."".$a."','".$_SESSION['s_colorDestaca']."');\" onMouseOut=\"libera('linha".$j."".$a."','".$_SESSION['s_colorLinPar']."','".$_SESSION['s_colorLinImpar']."');\"  onMouseDown=\"marca('linha".$j."".$a."','".$_SESSION['s_colorMarca']."');\">";

				$qryImg = "select * from imagens where img_oco = ".$rowDetail['numero']."";
				$execImg = mysqli_query($conect,$qryImg) or die (TRANS('MSG_ERR_RESCUE_INFO_IMAGE'));
				$rowTela = mysqli_fetch_array($execImg);
				$regImg = mysqli_num_rows($execImg);
				if ($regImg!=0) {
					//$linkImg = "<a onClick=\"javascript:popupWH('includes/functions/showImg.php?file=".$rowDetail['numero']."&cod=".$rowTela['img_cod']."',".$rowTela['img_largura'].",".$rowTela['img_altura'].")\"><img src='includes/icons/attach2.png'></a>";
					$linkImg = "<a onClick=\"javascript:popup_wide('./ocomon/geral/listFiles.php?COD=".$rowDetail['numero']."')\"><img src='includes/icons/attach2.png'></a>";
				} else $linkImg = "";

				$sqlSubCall = "select * from ocodeps where dep_pai = ".$rowDetail['numero']." or dep_filho=".$rowDetail['numero']."";
				$execSubCall = mysqli_query($conect,$sqlSubCall) or die (TRANS('MSG_ERR_RESCUE_INFO_SUBCALL').'<br>'.$sqlSubCall);
				$regSub = mysqli_num_rows($execSubCall);
				if ($regSub > 0) {
					#� CHAMADO PAI?
					$_sqlSubCall = "select * from ocodeps where dep_pai = ".$rowDetail['numero']."";
					$_execSubCall = mysqli_query($conect,$_sqlSubCall) or die (TRANS('MSG_ERR_RESCUE_INFO_SUBCALL').'<br>'.$_sqlSubCall);
					$_regSub = mysqli_num_rows($_execSubCall);
					$comDeps = false;
					while ($rowSubPai = mysqli_fetch_array($_execSubCall) ){
						$_sqlStatus = "select o.*, s.* from ocorrencias o, `status` s  where o.numero=".$rowSubPai['dep_filho']." and o.`status`=s.stat_id and s.stat_painel not in (3) ";
						$_execStatus = mysqli_query($conect,$_sqlStatus) or die (TRANS('MSG_ERR_RESCUE_INFO_STATUS_CALL_SON').'<br>'.$_sqlStatus);
						$_regStatus = mysqli_num_rows($_execStatus);
						if ($_regStatus > 0) {
							$comDeps = true;
						}
					}
					if ($comDeps) {
						$imgSub = "<img src='includes/icons/view_tree_red.png' width='16' height='16' title='".TRANS('FIELD_CALL_BOND_HANG')."'>";
					} else
						$imgSub =  "<img src='includes/icons/view_tree_green.png' width='16' height='16' title='".TRANS('FIELD_CALL_BOND_NOT_HANG')."'>";
				} else
					$imgSub = "";

				print "<td class='line'><a onClick=\"javascript: popup_alerta('./ocomon/geral/mostra_consulta.php?popup=true&numero=".$rowDetail['numero']."')\">".$rowDetail['numero']."</a> ".$imgSub."</TD>";

				//print "<td class='line'>".$rowDetail['numero']."</TD>";
				print "<td class='line'>".$linkImg."&nbsp;".$rowDetail['problema']."</TD>";
				print "<td class='line'><b>".$rowDetail['contato']."</b><br>".$rowDetail['telefone']."</TD>";
				$texto = trim($rowDetail['descricao']);
				if (strlen($texto)>200){
					$texto = substr($texto,0,195)." ..... ";
				};
				print "<td class='line'><b>".$rowDetail['setor']."</b><br>".$texto."</TD>";
				print "<td class='line'>".$rowDetail['nome']."</TD>";
				print "</TR>";
			}

			print "</table>";
			print "</div></td></tr>"; //status
			$a++;
		}
		print "</table>";
		print "</div></td></tr>"; //ocorrencias
		print "</table>";
		$a++;
		$b++;
	}

	print "</div></td></tr>"; //geral
	print "</table>";
	?>

	<script src='includes/javascript/libs/jquery-1-11-0.js' type='text/javascript'></script>
	<script src='includes/javascript/libs/modernizr.js'></script>
	<script src='includes/javascript/bootstrap.js' type='text/javascript'></script>
	<script language=javaScript>
	<!--
		function invertView(id) {
			var element = document.getElementById(id);
			var elementImg = document.getElementById('img'+id);
			var address = './includes/icons/';

			if (element.style.display=='none'){
				element.style.display='';
				elementImg.src = address+'close.png';
			} else {
				element.style.display='none';
				elementImg.src = address+'open.png';
			}
		}

	//-->
	</script>
	
</body>
</html>