import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../theme/app_theme.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('C-Net Store')),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const Text('Delivering in Bihar Sharif'),
        const SizedBox(height: 16),
        const TextField(
          readOnly: true,
          decoration: InputDecoration(
            hintText: 'Search products, groceries or food',
            prefixIcon: Icon(Icons.search),
          ),
        ),
        const SizedBox(height: 18),
        const Row(
          children: [
            Expanded(
              child: _ServiceCard(
                'Shopping',
                Icons.shopping_bag_outlined,
                AppColors.blue,
              ),
            ),
            Expanded(
              child: _ServiceCard(
                'Grocery',
                Icons.local_grocery_store_outlined,
                AppColors.green,
              ),
            ),
            Expanded(
              child: _ServiceCard(
                'Food',
                Icons.restaurant_outlined,
                AppColors.orange,
              ),
            ),
          ],
        ),
        const SizedBox(height: 22),
        Text('Top offers', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 10),
        SizedBox(
          height: 140,
          child: FutureBuilder<List<dynamic>>(
            future: ApiClient().banners(),
            builder: (_, snapshot) {
              final banners = snapshot.data ?? [];
              if (banners.isEmpty) {
                return const _Offer('Everything local, delivered');
              }
              return ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: banners.length,
                itemBuilder: (_, i) => SizedBox(
                  width: 280,
                  child: _Offer(
                    (banners[i] as Map<String, dynamic>)['title']?.toString() ??
                        'C-Net Store Offer',
                  ),
                ),
              );
            },
          ),
        ),
      ],
    ),
  );
}

class _ServiceCard extends StatelessWidget {
  const _ServiceCard(this.title, this.icon, this.color);
  final String title;
  final IconData icon;
  final Color color;
  @override
  Widget build(BuildContext context) => Card(
    child: Padding(
      padding: const EdgeInsets.all(12),
      child: Column(
        children: [
          Icon(icon, color: color),
          Text(title, textAlign: TextAlign.center),
        ],
      ),
    ),
  );
}

class _Offer extends StatelessWidget {
  const _Offer(this.title);
  final String title;
  @override
  Widget build(BuildContext context) => Card(
    color: AppColors.blue,
    child: Center(
      child: Text(
        title,
        style: const TextStyle(color: Colors.white, fontSize: 18),
      ),
    ),
  );
}
