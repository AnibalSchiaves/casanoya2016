<?php

class Application extends Controller{
	
	function Application()
	{
		parent::Controller();
		
	}
	
	function show($id, $deLos, $vetas){
			/*$this->db->where('id_cat', $id);
			$this->db->order_by('subcategoria asc');
			$query = $this->db->get('subcategorias');*/
			if($deLos == "true" && $vetas == "true"){
				$query = $this->db->query("select * from subcategorias where id_cat=$id");
			}else{
				$query = $this->db->query("select * from subcategorias where id_cat=$id and deLos=$deLos and vetas=$vetas");
			}
			$res = $query->result();
			$cat = array();
			if($res){
				foreach($res as $resul){
					$cat[] = array(
								'subcategoria' => $resul->subcategoria,
								'id' => $resul->id_sub
							);
				}
			}
			
			echo json_encode($cat);
	}	
	
	function showCombo($deLos, $vetas){
		/*$deLos = $_REQUEST['deLos'];
		$vetas = $_REQUEST['vetas'];*/
		
		/*$this->db->where('delos', $deLos);
		$this->db->where('vetas', $vetas);
		$query = $this->db->get('categorias');*/
		if($deLos && $vetas){
			$query = $this->db->query("select * from categorias where deLos=$deLos and vetas=$vetas");
		}else if($deLos){
			$query = $this->db->query("select * from categorias where deLos=$deLos");
		}else if($vetas){
			$query = $this->db->query("select * from categorias where vetas=$vetas");
		}else if(!$deLos && !$vetas){
			echo json_encode("");
		}
		if(isset($query)){
			$res = $query->result();
			
			$cat = array();
			if($res){
				foreach($res as $resul){
					$cat[] = array(
								'categoria' => $resul->categoria,
								'id' => $resul->id
							);
				}
			}		
			
			echo json_encode($cat);
		}
	}
	
	function show_marcas($id){
			$query = $this->db->query("select * from colores where marcas_id=$id");
			$res = $query->result();
			$cat = array();
			if($res){
				foreach($res as $resul){
					$cat[] = array(
								'color' => $resul->color,
								'id' => $resul->color_id
							);
				}
			}
			
			echo json_encode($cat);
	}
	
	function show_subcat($id){
			$query = $this->db->query("select art_id, nombre from articulos where subcatId=$id");
			$res = $query->result();
			$cat = array();
			if($res){
				foreach($res as $resul){
					$cat[] = array(
								'nombre' => $resul->nombre,
								'id' => $resul->art_id
							);
				}
			}
			
			echo json_encode($cat);
	}
	
	function deleteImg($id){
		$query = $this->db->query("select * from img where img_id=$id");
		$img = $query->result();
		@unlink($img[0]->org);
		@unlink($img[0]->big);
		@unlink($img[0]->sample);
		@unlink($img[0]->thumb);
		@unlink($img[0]->destacado);
		
		$query = $this->db->query("delete from img where img_id=$id");	
		if($query){
			echo json_encode("true/".$id);	
		}else{
			echo json_encode("false");	
		}
		
	}
	
	function deletePlano($id){
		$query = $this->db->query("select planos from articulos where art_id=$id");
		$plano = $query->result();
		@unlink($plano[0]->planos);
		
		$this->db->where('art_id', $id);
		$query = $this->db->update('articulos', array('planos'=>''));
		
		echo json_encode($query);
		
	}
	
	function saveRestrict($id_subcat, $id_marca, $brand){
		$query = $this->db->query("select * from restrict_sub where idSubcat=$id_subcat and idMarca=$id_marca");
		$res = $query->result();
		if(isset($res[0]->id_restrict)){
			$this->db->query("delete from restrict_sub where id_restrict=".$res[0]->id_restrict."");	
			$this->db->query("insert into restrict_sub (idSubcat,idMarca,brand) values($id_subcat,$id_marca,$brand)");
			echo json_encode("save");
		}else{
			$this->db->query("insert into restrict_sub (idSubcat,idMarca,brand) values($id_subcat,$id_marca,$brand)");	
			echo json_encode("save");
		}	
		
		
	}
	
	function deleteRestrict($id_subcat, $id_marca){
		$this->db->query("delete from restrict_sub where idSubcat=$id_subcat and idMarca=$id_marca");
	}
	
	function precios($art_id, $value){
		$test = $this->db->query("update articulos set precio=$value where art_id=$art_id");
		echo json_encode($test);
	}
	
	function destacados($brand){
		$random = array();
		if($brand == 0){
			$sector = 'delos';
		}else{
			$sector = 'vetas';	
		}
		
		$query = $this->db->query("SELECT a.art_id FROM articulos a, subcategorias s where a.destacado=1 and a.subcatId=s.id_sub and ".$sector."=1");
		foreach($query->result() as $row){
			$random[] = $row->art_id;
		}
		
		$art = array();
		if(count($random) > 0){
			if(count($random) <= 4){
				$top = 	count($random);
			}else{
				$top = 4;	
			}
			for($i=0; $i<$top; $i++){
				$rand_key = array_rand($random);
				$query = $this->db->query("select a.art_id, a.nombre, a.codigo from articulos a where art_id=".$random[$rand_key]."");	
				foreach($query->result() as $row){
					$query2 = $this->db->query("select destacado from img where artId=".$row->art_id." limit 1");
					$img = $query2->result();
					if(isset($img[0]->destacado)){
						$img = $img[0]->destacado;
					}else{
						$img = '';	
					}
					$art[] = array(
								'art_id' => $row->art_id,
								'nombre' => $row->nombre,
								'codigo' => $row->codigo,
								'img' => $img
							);
				}
				unset($random[$rand_key]); 
				$random = array_values($random);
			}
		}
		
		echo json_encode($art);
	}
}

?>