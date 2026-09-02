import 'package:flutter/material.dart';

import '../core/api_client.dart';
import 'image_library_screen.dart';
import 'product_form_screen.dart';

class ProductsScreen extends StatefulWidget {
  const ProductsScreen({super.key});

  @override
  State<ProductsScreen> createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
  late Future<Map<String, dynamic>> products;

  @override
  void initState() {
    super.initState();
    products = ApiClient().products();
  }

  void reload() => setState(() => products = ApiClient().products());

  Future<void> openForm([Map<String, dynamic>? product]) async {
    final changed = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => ProductFormScreen(product: product)),
    );
    if (changed == true) {
      reload();
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          title: const Text('Products & Inventory'),
          actions: [
            IconButton(
              tooltip: 'Smart Image Library',
              icon: const Icon(Icons.photo_library_outlined),
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ImageLibraryScreen()),
              ),
            ),
            IconButton(icon: const Icon(Icons.refresh), onPressed: reload),
          ],
        ),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: openForm,
          icon: const Icon(Icons.add),
          label: const Text('Add Product'),
        ),
        body: FutureBuilder<Map<String, dynamic>>(
          future: products,
          builder: (_, snapshot) {
            if (snapshot.connectionState != ConnectionState.done) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError) {
              return Center(child: FilledButton(onPressed: reload, child: const Text('Retry')));
            }
            final page = snapshot.data?['data'] as Map<String, dynamic>?;
            final items = page?['data'] as List<dynamic>? ?? <dynamic>[];
            if (items.isEmpty) {
              return Center(child: FilledButton.icon(onPressed: openForm, icon: const Icon(Icons.add), label: const Text('Add your first product')));
            }
            return RefreshIndicator(
              onRefresh: () async => reload(),
              child: ListView.builder(
                padding: const EdgeInsets.fromLTRB(12, 12, 12, 90),
                itemCount: items.length,
                itemBuilder: (_, index) {
                  final item = items[index] as Map<String, dynamic>;
                  final imageUrl = item['image_url']?.toString();
                  final active = item['is_active'] == true;
                  return Card(
                    child: ListTile(
                      onTap: () => openForm(item),
                      leading: imageUrl == null
                          ? const CircleAvatar(child: Icon(Icons.inventory_2_outlined))
                          : ClipRRect(borderRadius: BorderRadius.circular(8), child: Image.network(imageUrl, width: 58, height: 58, fit: BoxFit.cover)),
                      title: Text(item['name']?.toString() ?? 'Product'),
                      subtitle: Text('₹${item['sale_price'] ?? item['price'] ?? 0} • Stock ${item['stock_quantity'] ?? 0}\n${active ? 'Approved & Live' : 'Pending admin review'}'),
                      isThreeLine: true,
                      trailing: const Icon(Icons.edit_outlined),
                    ),
                  );
                },
              ),
            );
          },
        ),
      );
}
