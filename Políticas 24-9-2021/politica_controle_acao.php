<?
//$allowSession = "nao";
require_once("lib/configs.php");
require_once("multi_idioma_request.php");

// setar sessão com nome da página para ser usada no controle de acesso
$_SESSION["care-br"]["submodulo_pagina"] = "politica_controle.php";

// request
extract($_REQUEST);

if (empty($politica_ativo))
	$politica_ativo = "N";

// --------
// inclusão
// --------
if ($acao == "add"){
	
	$sql = "INSERT INTO tb_cad_politica SET
				politica_nome = '" . utf8_decode($politica_nome) . "',
				politica_descricao = '" . utf8_decode($politica_descricao) . "',
				politica_revisao = '" . utf8_decode($politica_revisao) . "',
				data_inicial = '" . fct_conversorData($data_inicial,3) . "',
				data_final = '" . fct_conversorData($data_final,3) . "',
				politica_ativo = '" . $politica_ativo . "', 
				usuario_id = '" . $_SESSION["care-br"]["usuario_id"] . "',
				data_update = now()";
	$conn->sql($sql);
	
	$politica_id = $conn->id();
	
	foreach($_FILES as $arquivo){
			
		$file = $arquivo['tmp_name'];
		$name_tmp = $arquivo['name'];
		
		$ar = pathinfo($name_tmp);
		$ext = $ar["extension"];
		$name_tmp = substr($name_tmp, 0, count($ext) * -1);
		
		$ext_type = array('gif','jpg','jpeg','png','bmp','pdf');
		
		$name = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $name_tmp);
		$name = str_replace(" ", "_", $name);
		
		$name_file = explode(".",$name);
	
		$name = $name_file[0] . "." . $ext;
		
		$filepath = 'anexo_politica/'.$name;
		
		
		if(file_exists("anexo_politica/" .$name)){
			$name = $name_file[0] . date("ymdhi") . "." . $ext;
			$filepath = 'anexo_politica/'.$name;
		}
		
		if(in_array($ext, $ext_type)){
			
			if(move_uploaded_file($file, $filepath)) { 

				$sql_files = "INSERT INTO tb_prod_politica_anexo SET politica_id = '$politica_id', politica_descricao = '" . utf8_decode($politica_descricao) . "',
				politica_revisao = '" . utf8_decode($politica_revisao) . "',
				politica_anexo_nome = '" . $filepath . "'";
				$conn->sql($sql_files);
				
				$politica_anexo_id = $conn->id();
				
				//gravar log revisão qdo inserir novo arquivo apenas
				$sql_rev = "INSERT INTO tb_prod_log_politica SET
				politica_anexo_id = '" . $politica_anexo_id . "',
				tipo = 'revisao',
				usuario_id = '" . $_SESSION["care-br"]["usuario_id"] . "',			
				data_update = now()";
				
				$conn->sql($sql_rev);
			
			}
		
		}
	
	}
	
}

// ---------
// alteração
// ---------
if ($acao == "updt"){
	
	$sql = "UPDATE tb_cad_politica SET
				politica_nome = '" . utf8_decode($politica_nome) . "',
				politica_descricao = '" . utf8_decode($politica_descricao) . "',
				data_inicial = '" . fct_conversorData($data_inicial,3) . "',
				data_final = '" . fct_conversorData($data_final,3) . "',
				politica_ativo = '" . $politica_ativo . "'
				WHERE politica_id = '" . $politica_id . "'";
	$conn->sql($sql);
	
	
	foreach($_FILES as $arquivo){
			
		$file = $arquivo['tmp_name'];
		$name_tmp = $arquivo['name'];
		
		$ar = pathinfo($name_tmp);
		$ext = $ar["extension"];
		$name_tmp = substr($name_tmp, 0, count($ext) * -1);
		
		$ext_type = array('gif','jpg','jpeg','png','bmp','pdf');
		
		$name = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $name_tmp);
		$name = str_replace(" ", "_", $name);
		
		$name_file = explode(".",$name);
	
		$name = $name_file[0] . "." . $ext;
		
		$filepath = 'anexo_politica/'.$name;
		
		
		if(file_exists("anexo_politica/" .$name)){
			$name = $name_file[0] . date("ymdhi") . "." . $ext;
			$filepath = 'anexo_politica/'.$name;
		}
		
		if(in_array($ext, $ext_type)){
			
			if(move_uploaded_file($file, $filepath)) {

				$sql_files = "INSERT INTO tb_prod_politica_anexo SET politica_id = '$politica_id', politica_descricao = '" . utf8_decode($politica_descricao) . "',
				politica_revisao = '" . utf8_decode($politica_revisao) . "',
				politica_anexo_nome = '" . utf8_decode($filepath) . "'";
				$conn->sql($sql_files);
				
				$politica_anexo_id = $conn->id();
				
				//gravar log revisão qdo inserir novo arquivo apenas
				$sql_rev = "INSERT INTO tb_prod_log_politica SET
				politica_anexo_id = '" . $politica_anexo_id . "',
				tipo = 'revisao',
				usuario_id = '" . $_SESSION["care-br"]["usuario_id"] . "',			
				data_update = now()";
				
				$conn->sql($sql_rev);

				$sql_updt = "UPDATE tb_cad_politica SET
				politica_revisao = '" . utf8_decode($politica_revisao) . "'
				WHERE politica_id = '" . $politica_id . "'";
				$conn->sql($sql_updt);
						
			}
		
		}
	
	}
	
}

// -------------------
// exportar para excel
// -------------------
if ($acao == "xls"){
	
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=politicas-".date('d-m-Y').".xls");


?>
	
	<table cellpadding="0" cellspacing="0" border="1" >
	<thead>
		<tr>
			<th class="text-center">Data</th>
			<th class="text-center">Postado por</th>
			<th class="text-center">Comentário</th>
			<th class="text-center">Revisão</th>
		</tr>
	</thead>
	<tbody>
	<?php
			

			// //buscar o empresacliente_id
			$sql_politica= "SELECT * FROM tb_cad_politica WHERE politica_ativo = 'S' ";
			$result_filtro = $conn->sql($sql_politica);
			while($tmp_filtro = mysqli_fetch_array($result_filtro)){
				$politica_id=$tmp_filtro["politica_id"];			
			


			$sql_linha = "select DATE_FORMAT(a.data_update, '%d/%m/%Y %H:%i:%s') as data_aceite, u.usuario_nome, CONCAT('De acordo com Doc: ', REPLACE(b.politica_anexo_nome,'anexo_politica/','')) as comentario, b.politica_revisao from tb_cad_usuario u inner join tb_prod_log_politica a on a.usuario_id = u.usuario_id inner join tb_prod_politica_anexo b on a.politica_anexo_id = b.politica_anexo_id inner join tb_cad_politica c on b.politica_id = c.politica_id and c.politica_id = '$politica_id' and a.tipo = 'aceite' ";
			
			$res_anexos = $conn->sql($sql_linha);
	while($tmp_anexos = mysqli_fetch_array($res_anexos)){

?>
	<tr>
	  <td><?=$tmp_anexos['data_aceite']?></td>
		<td><?=utf8_decode($tmp_anexos['usuario_nome'])?></td>
		<td><?=utf8_decode($tmp_anexos['comentario'])?></td>
		<td><?=utf8_decode($tmp_anexos['politica_revisao'])?></td>
	</tr>
	<? } }?>
	</tbody>
</table>
<?	}?>
<?
// --------
// exclusão
// --------
if ($acao == "dlt"){
	
	$arr_files = [];
	
	$sql = "DELETE FROM tb_cad_politica
				WHERE politica_id = '" . $politica_id . "'";
	$conn->sql($sql);
	
	$sql_sel = "SELECT politica_anexo_id, politica_anexo_nome FROM tb_prod_politica_anexo
				WHERE politica_id = '" . $politica_id . "'";
	$result_filtro = $conn->sql($sql_sel);
	
	while($tmp_filtro = mysqli_fetch_array($result_filtro)){
		
		array_push($arr_files, $tmp_filtro['politica_anexo_id']);
		
		$arq = str_replace("anexo_politica/","",$tmp_filtro['politica_anexo_nome']);
		
		if(file_exists('anexo_politica/'.$arq)){
			
			unlink('anexo_politica/'.$arq);
			
		}		 
	
	}
	
	if(!empty($arr_files)){
		
		$anexo_ids = implode(",",$arr_files);
		
		$sql_del_log = "DELETE FROM tb_prod_politica_anexo
				WHERE politica_id = '" . $politica_id . "'";
		$conn->sql($sql_del_log);
		
		$sql_del = "DELETE FROM tb_prod_log_politica
				WHERE politica_anexo_id IN($anexo_ids)";
		$conn->sql($sql_del);
				
	}
	
}


if ($acao == "dlt_anexo"){
	
	$sql_sel = "SELECT politica_anexo_nome FROM tb_prod_politica_anexo
				WHERE politica_anexo_id = '" . $politica_anexo_id . "'";
	$result_filtro = $conn->sql($sql_sel);
	
	while($tmp_filtro = mysqli_fetch_array($result_filtro)){
				
		$arq = str_replace("anexo_politica/","",$tmp_filtro['politica_anexo_nome']);
		
		if(file_exists('anexo_politica/'.$arq)){
			
			unlink('anexo_politica/'.$arq);
			
			$sql_del_log = "DELETE FROM tb_prod_log_politica
			WHERE politica_anexo_id = '" . $politica_anexo_id . "'";
			$conn->sql($sql_del_log);
			
			$sql_del = "DELETE FROM tb_prod_politica_anexo
			WHERE politica_anexo_id = '" . $politica_anexo_id . "'";
			$conn->sql($sql_del);
			
		}
		
	}
	
}

// ------------
// altera ativo
// ------------
if ($acao == "seta_ativo"){
	$sql = "UPDATE tb_cad_politica SET
				politica_ativo = '" . $politica_ativo . "'
				WHERE politica_id = '" . $politica_id . "'";
	$conn->sql($sql);
}


if ($acao == "grava_aceite"){
	
	$sql = "INSERT INTO tb_prod_log_politica SET
				politica_anexo_id = '" . $politica_anexo_id . "',
				tipo = 'aceite',
				usuario_id = '" . $_SESSION["care-br"]["usuario_id"] . "',			
				data_update = now()";
				
	$conn->sql($sql);
	
}

?>

<?
$conn->fechar();
?>
