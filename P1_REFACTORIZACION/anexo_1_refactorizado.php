<?php

class Product {
	// Funcion auxiliar para obtener la condicion de la query de stock bloqueado por pedidos en curso
	function obtenercondicionpedidoscurso ($productId){
		$sqlquery = "(order.status = '" . Order::STATUS_PENDING . "' OR order.status = '" . Order::STATUS_PROCESSING . "' OR order.status = '" . Order::STATUS_WAITING_ACCEPTANCE . "') AND order_line.product_id = $productId";
		return $sqlquery;
	}

	// Funcion auxiliar para obtener la condicion de la query de stock bloqueado
	function obtenercondicionbloqueado ($productId){
		$sqlquery = "blocked_stock.product_id = $productId AND blocked_stock_date > '" . date('Y-m-d H:i:s') . "' AND (shopping_cart_id IS NULL OR shopping_cart.status = '" . ShoppingCart::STATUS_PENDING . "')";
		return $sqlquery;
	}
	
	// Funcion auxiliar para obtener el scalar de stock bloqueado por pedidos en curso
	function funcionanonimapedidos ($productId,$queryselect,$querywhereorders){
		return (function ($db) use ($productId) {
			return OrderLine::find()->select($queryselect)->joinWith('order')->where($querywhereorders)->scalar();
		});
	}
	
	// Funcion auxiliar para obtener el scalar de stock bloqueado
	function funcionanonimabloqueado ($productId,$queryselect,$querywhereblocked){
		return (function ($db) use ($productId) {
			return BlockedStock::find()->select($queryselect)->joinWith('shoppingCart')->where($querywhereblocked)->scalar();
		});
	}

	// Funcion auxiliar que devuelve un quantity en funcion de si el securityStockConfig es distinto de vacio
	function funcionaplicarsecurity ($quantity, $securityStockConfig){
		if (!empty($securityStockConfig)) {
			$quantity = ShopChannel::applySecurityStockConfig(
				$quantity,
				@$securityStockConfig->mode,
				@$securityStockConfig->quantity
			);
		}
		return $quantity;
	}

	// Funcion Principal de stock
	public static function stock($productId,$quantityAvailable,$cache = false,$cacheDuration = 60,$securityStockConfig = null) {
		// Genero las diferentes variables necesarias
		$instance = new Product();
		$querywhereorders = $instance->obtenercondicionpedidoscurso($productId);		
		$querywhereblocked = $instance->obtenercondicionbloqueado($productId);
		$queryselect = "SUM(quantity) as quantity";
		$queryallpedidos = $instance->funcionanonimapedidos($productId,$queryselect,$querywhereorders);
		$queryallbloqueado = $instance->funcionanonimabloqueado($productId,$queryselect,$querywhereblocked);
		
		if($cache) {
			// Obtenemos el stock bloqueado por pedidos en curso
			$ordersQuantity = OrderLine::getDb()->cache($queryallpedidos, $cacheDuration);

            		// Obtenemos el stock bloqueado
			$blockedStockQuantity = BlockedStock::getDb()->cache($queryallbloqueado, $cacheDuration);
        	}else{
			// Obtenemos el stock bloqueado por pedidos en curso
			$ordersQuantity = $queryallpedidos;

			// Obtenemos el stock bloqueado
			$blockedStockQuantity = $queryallbloqueado;
        	}

		// Inicializamos variables si no estan definidas
		if (!isset($ordersQuantity)){
			$ordersQuantity = 0;
		}
		if (!isset($blockedStockQuantity)){
			$blockedStockQuantity = 0;
		}
		if (!isset($securityStockConfig->mode)){
			@$securityStockConfig->mode = "";
		}
		if (!isset($securityStockConfig->quantity)){
			$securityStockConfig->quantity = 0;
		}

		// Calculamos las unidades disponibles
		if ($quantityAvailable >= 0) {
			if (isset($ordersQuantity) || isset($blockedStockQuantity)) {
				$quantity = $quantityAvailable - @$ordersQuantity - @$blockedStockQuantity;
			}else{
				$quantity = $quantityAvailable;
			}
			$quantity = $this->funcionaplicarsecurity($quantity, $securityStockConfig);
			$quantityFin = $quantity > 0 ? $quantity : 0;
                        return $quantityFin;
		}else{
			return $quantityAvailable;
		}
		return 0;
	}
}

?>
