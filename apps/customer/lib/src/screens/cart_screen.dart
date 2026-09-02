import 'package:flutter/material.dart';

import '../core/api_client.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key, required this.version, required this.onCartChanged});

  final int version;
  final VoidCallback onCartChanged;

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final _api = ApiClient();
  List<dynamic> _carts = <dynamic>[];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void didUpdateWidget(CartScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.version != oldWidget.version) _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final carts = await _api.carts();
      if (!mounted) return;
      setState(() {
        _carts = carts;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Cart load नहीं हो सका।';
        _loading = false;
      });
    }
  }

  Future<void> _changeQuantity(int cartId, Map<String, dynamic> item, int quantity) async {
    if (quantity < 1) {
      await _remove(cartId, item['id'] as int);
      return;
    }
    await _api.updateCartItem(cartId, item['id'] as int, quantity);
    widget.onCartChanged();
  }

  Future<void> _remove(int cartId, int itemId) async {
    await _api.removeCartItem(cartId, itemId);
    widget.onCartChanged();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('My Cart')),
        body: RefreshIndicator(onRefresh: _load, child: _body()),
      );

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return ListView(children: [
        const SizedBox(height: 120),
        const Icon(Icons.cloud_off_outlined, size: 60),
        const SizedBox(height: 12),
        Text(_error!, textAlign: TextAlign.center),
        Center(child: TextButton(onPressed: _load, child: const Text('Retry'))),
      ]);
    }
    if (_carts.isEmpty) {
      return ListView(children: const [
        SizedBox(height: 120),
        Icon(Icons.shopping_cart_outlined, size: 72),
        SizedBox(height: 16),
        Text('Your cart is empty', textAlign: TextAlign.center,
            style: TextStyle(fontSize: 21, fontWeight: FontWeight.bold)),
        SizedBox(height: 8),
        Text('Shop से products जोड़ें।', textAlign: TextAlign.center),
      ]);
    }

    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      itemCount: _carts.length,
      itemBuilder: (context, index) {
        final cart = _carts[index] as Map<String, dynamic>;
        return _CartCard(
          cart: cart,
          onQuantity: (item, quantity) => _changeQuantity(cart['id'] as int, item, quantity),
          onRemove: (item) => _remove(cart['id'] as int, item['id'] as int),
        );
      },
    );
  }
}

class _CartCard extends StatelessWidget {
  const _CartCard({required this.cart, required this.onQuantity, required this.onRemove});

  final Map<String, dynamic> cart;
  final Future<void> Function(Map<String, dynamic>, int) onQuantity;
  final Future<void> Function(Map<String, dynamic>) onRemove;

  @override
  Widget build(BuildContext context) {
    final business = cart['business'] as Map<String, dynamic>?;
    final items = cart['items'] as List<dynamic>? ?? <dynamic>[];
    var total = 0.0;
    for (final raw in items) {
      final item = raw as Map<String, dynamic>;
      final product = item['product'] as Map<String, dynamic>;
      total += _price(product) * (item['quantity'] as num).toInt();
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Text(business?['name'] as String? ?? 'C-Net Store seller',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
          const Divider(),
          ...items.map((raw) {
            final item = raw as Map<String, dynamic>;
            final product = item['product'] as Map<String, dynamic>;
            final quantity = (item['quantity'] as num).toInt();
            return ListTile(
              contentPadding: EdgeInsets.zero,
              title: Text(product['name'] as String? ?? 'Product'),
              subtitle: Text('₹${_price(product).toStringAsFixed(2)}'),
              trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                IconButton(onPressed: () => onQuantity(item, quantity - 1),
                    icon: const Icon(Icons.remove_circle_outline)),
                Text('$quantity', style: const TextStyle(fontWeight: FontWeight.bold)),
                IconButton(onPressed: quantity >= 99 ? null : () => onQuantity(item, quantity + 1),
                    icon: const Icon(Icons.add_circle_outline)),
                IconButton(tooltip: 'Remove', onPressed: () => onRemove(item),
                    icon: const Icon(Icons.delete_outline)),
              ]),
            );
          }),
          const Divider(),
          Text('Subtotal: ₹${total.toStringAsFixed(2)}', textAlign: TextAlign.end,
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
          const SizedBox(height: 10),
          FilledButton(
            onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Checkout अगली release में चालू होगा।')),
            ),
            child: const Text('Proceed to Checkout'),
          ),
        ]),
      ),
    );
  }

  double _price(Map<String, dynamic> product) =>
      double.tryParse('${product['sale_price'] ?? product['price'] ?? 0}') ?? 0;
}
