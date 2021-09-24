<?
//$allowSession = "nao";
require_once("lib/configs.php");
require_once("multi_idioma_request.php");

// setar sessão com nome da página para ser usada no controle de acesso
$_SESSION["care-br"]["submodulo_pagina"] = "politica_controle.php";
?>

<?php include("header.php")?>
	<!-- dialog -->
	<script type="text/javascript" src="js/modern/dialog.js"></script>	
	
	<script type="text/javascript">
		$(document).ready(function() {
			loadGrid();
		});
		
		function loadGrid(){
			$.ajax({
			  type: "POST",
			  url: 'politica_controle_edicao.php',
			  data: $("#frm_grid").serialize(),
			  async: false,
			  success: function(data) {
				$("div#grid").html(data);
			  }
			});
		}
		
		function excluiCadastro(politica_id){
			if (confirm("<?=fct_get_var('global.php', 'var_confirma_exclusao', $_SESSION["care-br"]["idioma_id"])?>")){
				$.ajax({
				  type: "POST",
				  url: 'politica_controle_acao.php',
				  data: {acao: 'dlt', politica_id: politica_id},
				  async: false,
				  success: function(data) {
					loadGrid();
				  }
				});
			}
		}
		
		function dialogFoto(politica_id){
			
			$.Dialog({
				'title'      : 'Foto',
				'content'    : getFoto(politica_id),
				'draggable'  : true,
				'keepOpened' : true,
				'position'   : {
								'offsetY' : 30
				},																	
				'closeButton': true,
				'buttonsAlign': 'right',
				'buttons' : {
					
					'Cancelar' : {
						action: function(){
							return true;
						}
					}
				}
			});
			
		}

		function exporta_todos() {
			var filtro_sql = $("#filtro_sql").val();
			var formato = "xls";
			
			$("#sql").val(filtro_sql);
			$("#acao").val(formato);
			$("#frm_exportar").submit();

		}
		
		function getFoto(politica_id){
			
			var foto = "";
			
				$.ajax({
					 
					  type: "POST",
					  data: {acao: "get_anexos", politica_id: politica_id},
					  async: false,
					  url: 'politica_controle_edicao.php',
					  success: function(data) {
						foto = data;
					  }
					  
				}); 
											
			
			
			return foto;
		}
		
		
		function setaAtivo(politica_id){
			
			var politica_ativo = "";
			
			$("#politica_ativo_" + politica_id).each(function(){    
				if($(this).is(':checked'))
					politica_ativo = $(this).val();
			});

			$.ajax({
			  type: "POST",
			  url: 'politica_controle_acao.php',
			  data: {acao: 'seta_ativo', politica_id: politica_id, politica_ativo: politica_ativo},
			  async: false,
			  success: function(data) {
				loadGrid();
			  }
			});
		}
	</script>

    <div class="page secondary_">
        <div class="page-header">
            <div class="page-header-content titulo-internas">
                <h1><?=utf8_decode("Política")?> <small><?=fct_get_var('global.php', 'var_nome_modulo_grid', $_SESSION["care-br"]["idioma_id"])?></small></h1>
                <!--<a href="index.php" class="back-button big page-back" title="<?//=fct_get_var('global.php', 'var_home', $_SESSION["care-br"]["idioma_id"])?>" alt="<?//=fct_get_var('global.php', 'var_home', $_SESSION["care-br"]["idioma_id"])?>"></a>-->
            </div>
        </div>

        <div class="page-region">
            <div class="page-region-content">
				<ol class="unstyled three-columns icons" id="icons-list1">
					<form name="frm_exportar" id="frm_exportar" method="post" action="politica_controle_acao.php">
						<input type="hidden" id="acao" name="acao" value="" />
						<input type="hidden" id="sql" name="sql" value="" />				
					</form>
					<?=fct_get_rotina(VAR_MENU_CABECALHO, $_SESSION["care-br"]["submodulo_pagina"]);?>
				</ol>
				<form id="frm_grid" name="frm_grid" method="post">
					<input type="hidden" name="acao" id="acao" value="grid" />

					<div class="input-control text">
						<input type="text" id="grid_busca" name="grid_busca" placeholder="<?=fct_get_var('global.php', 'var_grid_pesquisa', $_SESSION["care-br"]["idioma_id"])?>" />
						<a href="javascript: void(0);" onclick="loadGrid(); return false;"><button class="btn-search"></button></a>
					</div>
					
					<!-- carregar grid aqui -->
					<div id="grid"></div>
					
					<!-- filtro -->
					<input type="hidden" name="filtro_categoria_titulo" id="filtro_categoria_titulo" value="" />
					<input type="hidden" name="filtro_categoria_ativo" id="filtro_categoria_ativo" value="" />
				</form>
            </div>
        </div>
    </div>

<?php include("footer.php")?>

<?
$conn->fechar();
?>
