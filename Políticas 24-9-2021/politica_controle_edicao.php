<?
//$allowSession = "nao";
require_once("lib/configs.php");
require_once("multi_idioma_request.php");

// setar sessão com nome da página para ser usada no controle de acesso
$_SESSION["care-br"]["submodulo_pagina"] = "politica_controle.php";

// request
$acao = $_REQUEST["acao"];
$politica_id = $_REQUEST["politica_id"];

// busca da grid
$grid_busca = utf8_decode($_REQUEST["grid_busca"]);

// filtro
$filtro_categoria_titulo = utf8_decode($_REQUEST["filtro_categoria_titulo"]);
$filtro_categoria_ativo = $_REQUEST["filtro_categoria_ativo"];

// ----
// grid
// ----
if ($acao == "grid"){
	?>
	<div id="pageContent">
		<table class="striped bordered hovered">
			<thead>
				<tr>
					<th class="text-center"><?=fct_get_var('global.php', 'var_id', $_SESSION["care-br"]["idioma_id"])?></th>
					<th><?=fct_get_var('global.php', 'var_nome', $_SESSION["care-br"]["idioma_id"])?></th>	
					<th class="text-center"><?=fct_get_var('global.php', 'var_descricao', $_SESSION["care-br"]["idioma_id"])?></th>
					<th class="text-center"><?=utf8_decode("Revisão")?></th>
					<th class="text-center"><?=utf8_decode("Vigência")?></th>
					<? if($_SESSION["care-br"]["perfil_id"] == 457) {?>
					<th class="text-center"><?=fct_get_var('global.php', 'var_ativo', $_SESSION["care-br"]["idioma_id"])?></th>
					<? } ?>
					<th class="text-center"><?=fct_get_var('global.php', 'var_grid_acao', $_SESSION["care-br"]["idioma_id"])?></th>
				</tr>
			</thead>
			<tbody>
				<?
				// filtro
				$filtro_where = "1=1";
				$filtro_query = "";
				
				if($_SESSION["care-br"]["perfil_id"] != 457)
					$filtro_query = " WHERE politica_ativo = 'S' ";
				
				// selecionar dados
				$sql = "select a.politica_id, a.politica_nome, a.politica_descricao, a.politica_revisao, a.politica_ativo, CONCAT(DATE_FORMAT(a.data_inicial,'%d/%m/%Y'), ' - ' , DATE_FORMAT(a.data_final,'%d/%m/%Y')) as vigencia
				from tb_cad_politica a $filtro_query";
				//echo $sql;
				
				// ---------
				// paginação
				// ---------
				$result_paginacao = $conn->sql($sql);
				$registro_total = mysqli_num_rows($result_paginacao);
				$pagina_corrente = $_REQUEST["page"];
				if (empty($pagina_corrente))
					$pagina_corrente = 1;
				$pagina_tamanho = 5;
				$pagina_total = ceil($registro_total / $pagina_tamanho);
				$inicio = ($pagina_corrente-1) * $pagina_tamanho;
				/*
				if ($inicio >= $pagina_total){
					$inicio = 0;
					$pagina_corrente = 1;
				}
				*/
				$sql .= " LIMIT " . $inicio . "," . $pagina_tamanho;
				// -------------
				// fim paginação
				// -------------
				?>

				<input type="hidden" name="filtro_sql" id="filtro_sql" value="<?=$sql?>" />
				<?
				$result_cadastro = $conn->sql($sql);
				if (mysqli_num_rows($result_cadastro) == 0){
					?>
					<tr>
						<td colspan="5" class="text-center"><?=fct_get_var('global.php', 'var_nenhum_registro_encontrado', $_SESSION["care-br"]["idioma_id"])?></td>
					</tr>
					<?
				}else{
					while($dados_cadastro = mysqli_fetch_array($result_cadastro)){
						if ($dados_cadastro["politica_ativo"] == "S")
							$politica_ativo = fct_get_var('global.php', 'var_sim', $_SESSION["care-br"]["idioma_id"]);
						else
							$politica_ativo = fct_get_var('global.php', 'var_nao', $_SESSION["care-br"]["idioma_id"]);
						?>
						<tr>
							<td class="text-center"><?=$dados_cadastro["politica_id"]?></td>
							<td><?=$dados_cadastro["politica_nome"]?></td>
							<td><?=$dados_cadastro["politica_descricao"]?></td>
							<td><?=$dados_cadastro["politica_revisao"]?></td>
							<td><?=$dados_cadastro["vigencia"]?></td>
							<? if($_SESSION["care-br"]["perfil_id"] == 457) { ?>
							<td class="text-center">
								<label class="input-control switch" alt="<?=$politica_ativo?>" title="<?=$politica_ativo?>" onchange="setaAtivo('<?=$dados_cadastro["politica_id"]?>');">
									<input type="checkbox" id="politica_ativo_<?=$dados_cadastro["politica_id"]?>" value="S" <? if ($dados_cadastro["politica_ativo"] == "S") echo "checked"; ?> />
									<span class="helper"><?//=$categoria_ativo?></span>
								</label>
							</td>
							<? } ?>
							<td class="text-center">
								<?=fct_get_rotina(VAR_MENU_INTERNO, $_SESSION["care-br"]["submodulo_pagina"], $dados_cadastro["politica_id"]);?>
							</td>
						</tr>
						<?
					}
				}
				?>
			</tbody>
			<tfoot></tfoot>
		</table>
	
		<?
		// ---------
		// paginação
		// ---------
		if ($pagina_total > 1){
			?>
			<div id="pageDiv"></div>
			<script type="text/javascript" src="js/modern/pagelist.js"></script>
			<script type="text/javascript">
				$(document).ready(function(){
					var page = $("#pageDiv").pagelist();
					page.total = <?=$pagina_total?>;
					page.current = <?=$pagina_corrente?>;

					// variáveis de filtro e busca
					var grid_busca = $("#grid_busca").val();
					var filtro_categoria_titulo = $("#filtro_categoria_titulo").val();
					var filtro_categoria_ativo = $("#filtro_categoria_ativo").val();
					
					page.url = "politica_controle_edicao.php?page={page}&acao=grid&grid_busca=" + grid_busca + "&filtro_categoria_titulo=" + filtro_categoria_titulo + "&filtro_categoria_ativo=" + filtro_categoria_ativo;
					page.ajax = "#pageContent";
					page.create();
				});
			</script>
			<?
		}
		// -------------
		// fim paginação
		// -------------
		?>
	</div>
	<?
}

// ---------------------
// inclusão ou alteração
// ---------------------
if (($acao == "add") || ($acao == "updt")){
	include("header.php");
	
	// dados do cadastro
	if (!empty($politica_id)){
		$sql = "SELECT a.*  
				FROM tb_cad_politica a
				 inner join tb_prod_politica_anexo b on a.politica_id = b.politica_id left join tb_prod_log_politica c on b.politica_anexo_id = c.politica_anexo_id
				  WHERE a.politica_id = '" . $politica_id . "'
				 ";
		$result_cadastro = $conn->sql($sql);
		while($tmp_cadastro = mysqli_fetch_array($result_cadastro)){
			$dados_cadastro[] = $tmp_cadastro;
		}
	}
	?>

	<!-- validator -->
	<script type="text/javascript" src="js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="js/funcoes_jquery.js"></script>
	<script type="text/javascript" src="js/datepicker-idiomas.js"></script>
	<!-- fim validator -->
	
	<script type="text/javascript">
		$(document).ready(function() {
			
			$(".mascara_ddmmyyyy").datepicker({ dateFormat: 'dd/mm/yy' });

			$("body").on("click", "#politica_ativo", function(){
				
				if ($(this).is(':checked'))
					$(this).val('S')
				else
					$(this).val('N');	

			});
			
			// validar dados
			$('#frm_cadastro').validate({
				rules:{
					politica_descricao: "required",
					politica_nome: "required"
				},
				messages:{
					politica_descricao: "<?=fct_get_var('global.php', 'var_campo_obrigatorio', $_SESSION["care-br"]["idioma_id"])?>",
					politica_nome: "<?=fct_get_var('global.php', 'var_campo_obrigatorio', $_SESSION["care-br"]["idioma_id"])?>"
				},
				submitHandler: function(form) {
		
					if (confirm("<?=fct_get_var('global.php', 'var_confirma_cadastro', $_SESSION["care-br"]["idioma_id"])?>")){
						
						var form_data = new FormData();                  
						form_data.append('politica_id', $("#politica_id").val());
						form_data.append('acao', $("#acao").val());
						form_data.append('data_inicial', $("#data_inicial").val());
						form_data.append('data_final', $("#data_final").val());
						form_data.append('politica_nome', $("#politica_nome").val());
						form_data.append('politica_descricao', $("#politica_descricao").val());
						form_data.append('politica_revisao', $("#politica_revisao").val());
						form_data.append('politica_ativo', $("#politica_ativo").val());
						form_data.append('file', $('#attachment').prop('files')[0]);
						
							$.ajax({
								type: "POST",
								url: 'politica_controle_acao.php',
								dataType: 'text',
								cache: false,
								contentType: false,
								processData: false,
								data: form_data,
								success: function(data) {
									
									window.location.href="politica_controle.php";
									
								}
							});
						
						
					}
					// não submeter formulário, gravar via AJAX
					return false;
				}
			});
		});
	</script>
	
	<div class="page secondary">
        <div class="page-header">
            <div class="page-header-content titulo-internas">
      			<h1><?=utf8_decode("Política")?> <small><?=fct_get_var('global.php', 'var_nome_modulo_edicao', $_SESSION["care-br"]["idioma_id"])?></small></h1>
				<a href="politica_controle.php" class="back-button big page-back" title="<?=fct_get_var('global.php', 'var_retornar', $_SESSION["care-br"]["idioma_id"])?>" alt="<?=fct_get_var('global.php', 'var_retornar', $_SESSION["care-br"]["idioma_id"])?>"></a>
            </div>
        </div>

        <div class="page-region">
            <div class="form-pags span12">
				<div class="grid">
					<form id="frm_cadastro" name="frm_cadastro" method="post">
						<input type="hidden" name="acao" id="acao" value="<?=$acao?>" />
						<input type="hidden" name="politica_id" id="politica_id" value="<?=$politica_id?>" />
						<div class="row">
							<div class="span5 campos-form">
								<label><?=fct_get_var('global.php', 'var_nome', $_SESSION["care-br"]["idioma_id"])?></label>
								<div class="input-control text">
									<input type="text" id="politica_nome" name="politica_nome" value="<?=$dados_cadastro[0]["politica_nome"]?>" autofocus />
									<button class="btn-clear" />
								</div>
							</div>

							<div class="span5 campos-form">
								<label><?=fct_get_var('global.php', 'var_descricao', $_SESSION["care-br"]["idioma_id"])?></label>
								<div class="input-control text">
									<input type="text" id="politica_descricao" name="politica_descricao" value="<?=$dados_cadastro[0]["politica_descricao"]?>"/>
									<button class="btn-clear" />
								</div>
							</div>

							<div class="span5 campos-form">
								<label><?=utf8_decode("Revisão")?></label>
								<div class="input-control text">
									<input type="text" id="politica_revisao" name="politica_revisao" value="<?=$dados_cadastro[0]["politica_revisao"]?>"/>
									<button class="btn-clear" />
								</div>
							</div>
							
							<div class="span5 campos-form">
								<label><?=utf8_decode("Data Inicial Vigência")?></label>
								<div class="input-control text">
									<input type="text" id="data_inicial" class="mascara_ddmmyyyy" name="data_inicial" value="<?=fct_conversorData($dados_cadastro[0]["data_inicial"],4)?>"/>
									<button class="btn-clear" />
								</div>
							</div>
							
							<div class="span5 campos-form">
								<label><?=utf8_decode("Data Final Vigência")?></label>
								<div class="input-control text">
									<input type="text" id="data_final" class="mascara_ddmmyyyy" name="data_final" value="<?=fct_conversorData($dados_cadastro[0]["data_final"],4)?>"/>
									<button class="btn-clear" />
								</div>
							</div>

							<div class="span5 campos-form">
								<label><?=fct_get_var('global.php', 'var_ativo', $_SESSION["care-br"]["idioma_id"])?></label>
                                <label class="input-control switch">
                                    <input type="checkbox" id="politica_ativo" name="politica_ativo" value="S" <? if (($dados_cadastro[0]["politica_ativo"] == "S") || (empty($dados_cadastro[0]["politica_ativo"]))) echo "checked"; ?> />
                                    <span class="helper"></span>
                                </label>
							</div>
						</div>
					
					
						<div class="span5 campos-form">
								<div class="input-control select">
									<label>
										Upload Arquivo
										
										<div class="tooltip">Upload Arquivo</div>
									</label>
									
									<input type="file" name="attachment" id="attachment" style="margin-top: 9px; margin-bottom: 9px;">
									<div id="files_list">
									
									</div>
								</div>
							</div>
							
						<div class="row">	
							<div class="span9 campos-form">
					
								<table class="striped bordered hovered" style="margin-left:20px">
									<thead>
										<tr>
											<th class="text-left" style="background:#2D89EF;color:white;font-weight:bold" colspan="4"><?=utf8_decode('Log - Aceite Política')?></th>
										</tr>
										<tr>
											<th class="text-center">Data</th>
											<th class="text-center">Postado por</th>
											<th class="text-center"><?=utf8_decode("Comentário")?></th>
											<th class="text-center"><?=utf8_decode("Revisão")?></th>
										</tr>
									</thead>
									<tbody>
									
									<?
									
									$sql_anexos = "select DATE_FORMAT(a.data_update, '%d/%m/%Y %H:%i:%s') as data_aceite, u.usuario_nome, CONCAT('De acordo com Doc: ', REPLACE(b.politica_anexo_nome,'anexo_politica/','')) as comentario, b.politica_revisao from tb_cad_usuario u inner join tb_prod_log_politica a on a.usuario_id = u.usuario_id inner join tb_prod_politica_anexo b on a.politica_anexo_id = b.politica_anexo_id inner join tb_cad_politica c on b.politica_id = c.politica_id and c.politica_id = '$politica_id' and a.tipo = 'aceite'";
									$res_anexos = $conn->sql($sql_anexos);
									while($tmp_anexos = mysqli_fetch_array($res_anexos)){
										
										?>
										<tr><td><?=$tmp_anexos['data_aceite']?></td><td><?=utf8_decode($tmp_anexos['usuario_nome'])?></td><td><?=utf8_decode($tmp_anexos['comentario'])?></td><td><?=utf8_decode($tmp_anexos['politica_revisao'])?></td></tr>
										<?
										
									}	
									
									?>		
									
									</tbody>
								</table>
							</div>
							
							<div class="span9 campos-form">
					
								<table class="striped bordered hovered" style="margin-left:20px">
									<thead>
										<tr>
											<th style="background:#2D89EF;color:white;font-weight:bold" class="text-left" colspan="3"><?=utf8_decode('Log - Revisões')?></th>
										</tr>
										<tr>
											<th class="text-center">Data</th>
											<th class="text-center">Postado por</th>
											<th class="text-center"><?=utf8_decode("Comentário")?></th>
										</tr>
									</thead>
									<tbody>
									
									<?
									
									$sql_anexos = "select DATE_FORMAT(a.data_update, '%d/%m/%Y %H:%i:%s') as data_aceite, u.usuario_nome, CONCAT('Atualização Revisão: ', b.politica_revisao) as comentario from tb_cad_usuario u inner join tb_prod_log_politica a on a.usuario_id = u.usuario_id inner join tb_prod_politica_anexo b on a.politica_anexo_id = b.politica_anexo_id inner join tb_cad_politica c on b.politica_id = c.politica_id and c.politica_id = '$politica_id' and a.tipo = 'revisao'";
									$res_anexos = $conn->sql($sql_anexos);
									while($tmp_anexos = mysqli_fetch_array($res_anexos)){
										
										?>
										<tr><td><?=$tmp_anexos['data_aceite']?></td><td><?=utf8_decode($tmp_anexos['usuario_nome'])?></td><td><?=utf8_decode($tmp_anexos['comentario'])?></td></tr>
										<?
										
									}	
									
									?>		
									
									</tbody>
								</table>
							</div>
							
						</div>
				
						<div class="row">
							<div class="btn-salvar">
								<input type="reset" value="<?=fct_get_var('global.php', 'var_botao_redefinir', $_SESSION["care-br"]["idioma_id"])?>"><input type="submit" value="<?=fct_get_var('global.php', 'var_botao_confirmar', $_SESSION["care-br"]["idioma_id"])?>">
							</div>
						</div>
					</form>
									
				</div>
			</div>
        </div>
    </div>
	<?
	include("footer.php");
}


if ($acao == "get_anexos"){
	
	?>
	<script type="text/javascript" src="js/modern/dialog_modal.js"></script>
	<script type="text/javascript">
	
	
		$(document).ready(function() {
			
			$("body").on("click", ".aceitar-doc", function(){
				
				var id = $(this).attr('id');
				dialogFoto2(id);

			});	
		
		});
				
		function excluirAnexo(id) {
			
			$.ajax({

				type: "POST",
				data: {acao: "dlt_anexo", politica_anexo_id: id},
				async: false,
				url: 'politica_controle_acao.php',
				success: function(data) {
					
					$(".politica-anexo#"+id).closest("tr").remove();
					alert("Arquivo excluído com sucesso!");
					
				}

			});
			
		}		
		
		function dialogFoto2(id) {
			
			setTimeout(function() { 
				$("#dialogBox").hide();
			}, 100);	

			$.DialogModal({
				'title'      : 'Foto',
				'content'    :'<div style="padding:15px;background:grey"><input type="checkbox" style="transform: scale(1.2)" id="check_politica_anexo" name="check_politica_anexo"><label for="check_politica_anexo" style="color: white; margin-left:5px; font-size:90%; font-weight: bold"> Declaro que estou de acordo com a pol&iacute;tica</label></div>',
				'draggable'  : true,
				'keepOpened' : true,
				'position'   : {
								'offsetY' : 30
				},																	
				'close': true,
				'buttonsAlign': 'right',
				'buttons' : {
					
					'OK' : {
						action: function(){
							
							$("#dialogBox").show();
							
							if($('#check_politica_anexo').is(':checked')){					
							
								$.ajax({

									type: "POST",
									data: {acao: "grava_aceite", politica_anexo_id: id},
									async: false,
									url: 'politica_controle_acao.php',
									success: function(data) {
										
										alert("Declara\u00e7\u00e3o gravada com sucesso!");
										$(".aceitar-doc#"+id).closest("tr").find(".text-aceite").val('OK');
										$(".aceitar-doc#"+id).removeClass("aceitar-doc");
										
									}

								});	
					
							}else{
								alert("Por favor aceite que est\u00e1 de acordo com a pol\u00edtica!");
							} 
		
						}
					
					},
		
					'Cancelar' : {
						action: function(){
							$("#dialogBox").show();			
						}
					}
				}
			});
		}
			
			
	</script>
	
	<?
	
	$politica_id = $_REQUEST["politica_id"];
	
	$sql = "select * from tb_prod_politica_anexo where politica_id = '$politica_id' order by politica_anexo_id desc"; 
	$r = $conn->sql($sql);
	$html = '<table class="bordered striped hovered" id="grid-revisao"><thead><tr><th>Foto</th><th>' . utf8_decode("Revisão") . '</th><th>' . utf8_decode("Descrição") . '</th></tr></thead><tbody>';
	
	while($tmp_dados = mysqli_fetch_array($r)){
		
		$classe = '';
		$texto = 'OK';
		$sql_aceite = "select politica_anexo_id from tb_prod_log_politica where politica_anexo_id = '" . $tmp_dados['politica_anexo_id'] . "' and usuario_id = '" . $_SESSION["care-br"]["usuario_id"] . "' and tipo = 'aceite'";
		$r_aceite = $conn->sql($sql_aceite);
		
		if(mysqli_num_rows($r_aceite) == 0){//ainda não declarou que aceita o documento
		
			$classe = ' aceitar-doc';
			$texto = 'Pendente';
		}
		
		$html .= '<tr><td align="center"><a class = "politica-anexo' . $classe . '" id = "' . $tmp_dados['politica_anexo_id'] . '" href="/websolution/' . $tmp_dados['politica_anexo_nome'] . '" target="_blank"><i class="icon-file-pdf" style="font-size: 50px"></i></a></td>
		<td><div class="input-control textarea"><textarea rows="3" disabled>' . $tmp_dados['politica_descricao'] . '</textarea></div></td>
		<td><div class="input-control textarea"><textarea rows="3" disabled>' . $tmp_dados['politica_revisao'] . '</textarea></div></td>
		<td><div class="input-control textarea"><textarea class="text-aceite" rows="3" disabled>' . $texto . '</textarea></div></td>
		<td align="center" width="30"><a href="javascript: void(0);" onclick="excluirAnexo(' . $tmp_dados['politica_anexo_id'] . ')"><i class="icon-cancel" style="font-size: 20px"></i></button></a></td></tr>';		
		
	}
	
	$html .= '</tbody></table>';
	
	echo $html;
	
}



?>

<?
$conn->fechar();
?>
