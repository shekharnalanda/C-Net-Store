import 'package:flutter/material.dart';

import '../core/app_config.dart';
import '../core/token_store.dart';
import 'login_screen.dart';

class AccountScreen extends StatelessWidget {
  const AccountScreen({super.key});
  @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('My Account')), body: ListView(padding: const EdgeInsets.all(16), children: [
    const Card(child: ListTile(contentPadding: EdgeInsets.all(16), leading: CircleAvatar(radius: 28, child: Icon(Icons.person)), title: Text('C-Net Store Customer', style: TextStyle(fontWeight: FontWeight.bold)), subtitle: Text('Manage profile and addresses'))), const SizedBox(height: 12),
    const _Item(icon: Icons.receipt_long_outlined, title: 'My Orders'), const _Item(icon: Icons.favorite_border, title: 'Wishlist'), const _Item(icon: Icons.location_on_outlined, title: 'Delivery Addresses'), const _Item(icon: Icons.support_agent, title: 'Help & Support', subtitle: '${AppConfig.supportPhone} • ${AppConfig.supportEmail}'), const _Item(icon: Icons.language, title: 'Language', subtitle: 'English / हिन्दी'),
    const SizedBox(height: 12), OutlinedButton.icon(onPressed: () async { await const TokenStore().clear(); if (!context.mounted) return; Navigator.of(context).pushAndRemoveUntil(MaterialPageRoute(builder: (_) => const LoginScreen()), (_) => false); }, icon: const Icon(Icons.logout), label: const Text('Logout')),
  ]));
}
class _Item extends StatelessWidget { const _Item({required this.icon, required this.title, this.subtitle}); final IconData icon; final String title; final String? subtitle; @override Widget build(BuildContext context) => Card(child: ListTile(leading: Icon(icon), title: Text(title), subtitle: subtitle == null ? null : Text(subtitle!), trailing: const Icon(Icons.chevron_right), onTap: () {})); }

