import 'package:flutter/material.dart';

import '../core/api_client.dart';

class CatalogScreen extends StatefulWidget {
  const CatalogScreen({super.key, required this.onCartChanged});

  final VoidCallback onCartChanged;

  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  final _api = ApiClient();
  final _search = TextEditingController();
  List<dynamic> _products = <dynamic>[];
  List<dynamic> _categories = <dynamic>[];
  String? _type;
  int? _categoryId;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final response = await _api.catalog(
        type: _type,
        categoryId: _categoryId,
        query: _search.text.trim(),
      );
      if (!mounted) return;
      setState(() {
        _products = response['data'] as List<dynamic>? ?? <dynamic>[];
        _categories =
            response['categories'] as List<dynamic>? ?? <dynamic>[];
        _loading = false;
      });
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _error = 'Catalog load nahi ho saka. Dobara try karein.';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('Shop')),
        body: RefreshIndicator(
          onRefresh: _load,
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16),
            children: [
              SearchBar(
                controller: _search,
                hintText: 'Search products',
                leading: const Icon(Icons.search),
                trailing: [
                  if (_search.text.isNotEmpty)
                    IconButton(
                      onPressed: () {
                        _search.clear();
                        _load();
                      },
                      icon: const Icon(Icons.clear),
                    ),
                ],
                onSubmitted: (value) => _load(),
              ),
              const SizedBox(height: 14),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _typeChip('All', null),
                    _typeChip('Shopping', 'shopping'),
                    _typeChip('Grocery', 'grocery'),
                    _typeChip('Food', 'food'),
                  ],
                ),
              ),
              if (_categories.isNotEmpty) ...[
                const SizedBox(height: 10),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _categoryChip('All categories', null),
                      ..._categories.map((item) {
                        final category = item as Map<String, dynamic>;
                        return _categoryChip(
                          category['name'] as String? ?? 'Category',
                          category['id'] as int?,
                        );
                      }),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 18),
              if (_loading)
                const Padding(
                  padding: EdgeInsets.only(top: 80),
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (_error != null)
                _Message(
                  icon: Icons.cloud_off_outlined,
                  text: _error!,
                  action: _load,
                )
              else if (_products.isEmpty)
                const _Message(
                  icon: Icons.search_off,
                  text: 'Koi matching product nahi mila.',
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: _products.length,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 0.66,
                  ),
                  itemBuilder: (context, index) =>
                      _ProductCard(
                        product: _products[index],
                        onAdded: widget.onCartChanged,
                      ),
                ),
            ],
          ),
        ),
      );

  Widget _typeChip(String label, String? value) => Padding(
        padding: const EdgeInsets.only(right: 8),
        child: ChoiceChip(
          label: Text(label),
          selected: _type == value,
          onSelected: (selected) {
            if (!selected) return;
            setState(() {
              _type = value;
              _categoryId = null;
            });
            _load();
          },
        ),
      );

  Widget _categoryChip(String label, int? value) => Padding(
        padding: const EdgeInsets.only(right: 8),
        child: ChoiceChip(
          label: Text(label),
          selected: _categoryId == value,
          onSelected: (selected) {
            if (!selected) return;
            setState(() => _categoryId = value);
            _load();
          },
        ),
      );
}

class _ProductCard extends StatefulWidget {
  const _ProductCard({required this.product, required this.onAdded});

  final dynamic product;
  final VoidCallback onAdded;

  @override
  State<_ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends State<_ProductCard> {
  bool _adding = false;

  Future<void> _add() async {
    setState(() => _adding = true);
    try {
      final item = widget.product as Map<String, dynamic>;
      await ApiClient().addCartItem({'product_id': item['id'], 'quantity': 1});
      widget.onAdded();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Product cart में जोड़ दिया गया।')),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Cart में नहीं जुड़ा। फिर कोशिश करें।')),
      );
    } finally {
      if (mounted) setState(() => _adding = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final item = widget.product as Map<String, dynamic>;
    final business = item['business'] as Map<String, dynamic>?;
    final imageUrl = item['image_url'] as String?;
    final price = item['sale_price'] ?? item['price'] ?? 0;

    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AspectRatio(
            aspectRatio: 1,
            child: imageUrl == null
                ? const ColoredBox(
                    color: Color(0xFFF1F3F5),
                    child: Icon(Icons.inventory_2_outlined, size: 42),
                  )
                : Image.network(
                    imageUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) =>
                        const Icon(Icons.broken_image_outlined),
                  ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item['name'] as String? ?? 'Product',
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w700),
                  ),
                  const Spacer(),
                  Text(
                    business?['name'] as String? ?? 'C-Net Store seller',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '₹$price',
                    style: TextStyle(
                      color: Theme.of(context).colorScheme.primary,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 6),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: _adding ? null : _add,
                      icon: _adding
                          ? const SizedBox.square(
                              dimension: 16,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.add_shopping_cart, size: 18),
                      label: Text(_adding ? 'Adding...' : 'Add'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _Message extends StatelessWidget {
  const _Message({required this.icon, required this.text, this.action});

  final IconData icon;
  final String text;
  final VoidCallback? action;

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(top: 70),
        child: Column(
          children: [
            Icon(icon, size: 52),
            const SizedBox(height: 12),
            Text(text, textAlign: TextAlign.center),
            if (action != null) ...[
              const SizedBox(height: 12),
              FilledButton(onPressed: action, child: const Text('Retry')),
            ],
          ],
        ),
      );
}
