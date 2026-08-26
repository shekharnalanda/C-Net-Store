import 'package:flutter/material.dart';

class CatalogScreen extends StatelessWidget {
  const CatalogScreen({super.key});
  @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('Categories')), body: GridView.count(padding: const EdgeInsets.all(16), crossAxisCount: 2, mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: 1.25, children: const [
    _Category(title: 'Electronics', icon: Icons.devices), _Category(title: 'Fashion', icon: Icons.checkroom), _Category(title: 'Grocery', icon: Icons.local_grocery_store), _Category(title: 'Restaurants', icon: Icons.restaurant), _Category(title: 'Home & Kitchen', icon: Icons.chair_outlined), _Category(title: 'Beauty', icon: Icons.spa_outlined),
  ]));
}
class _Category extends StatelessWidget { const _Category({required this.title, required this.icon}); final String title; final IconData icon; @override Widget build(BuildContext context) => Card(child: InkWell(borderRadius: BorderRadius.circular(12), onTap: () {}, child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(icon, size: 38, color: Theme.of(context).colorScheme.primary), const SizedBox(height: 10), Text(title, textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w700))]))); }

