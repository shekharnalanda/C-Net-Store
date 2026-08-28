import 'package:flutter/material.dart';

import '../core/api_client.dart';
import 'image_library_screen.dart';

class ProductsScreen extends StatelessWidget {
  const ProductsScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Products & Inventory'), actions: [IconButton(tooltip: 'Smart Image Library', icon: const Icon(Icons.photo_library_outlined), onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ImageLibraryScreen())))]),
    body: FutureBuilder<Map<String, dynamic>>(
      future: ApiClient().products(),
      builder: (_, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        final page = snapshot.data?['data'] as Map<String, dynamic>?;
        final items = page?['data'] as List<dynamic>? ?? [];
        if (items.isEmpty) {
          return const Center(child: Text('No products yet.'));
        }
        return ListView.builder(
          padding: const EdgeInsets.all(16),
          itemCount: items.length,
          itemBuilder: (_, index) {
            final item = items[index] as Map<String, dynamic>;
            return Card(
              child: ListTile(
                leading: const Icon(Icons.inventory_2_outlined),
                title: Text(item['name']?.toString() ?? 'Product'),
                subtitle: Text(
                  '₹${item['sale_price'] ?? item['price'] ?? 0} • Stock ${item['stock_quantity'] ?? 0}',
                ),
              ),
            );
          },
        );
      },
    ),
  );
}
