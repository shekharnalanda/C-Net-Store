import 'package:flutter/material.dart';

import '../core/token_store.dart';
import 'login_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Partner Profile')),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const Card(
          child: ListTile(
            contentPadding: EdgeInsets.all(16),
            leading: CircleAvatar(
              radius: 28,
              child: Icon(Icons.delivery_dining),
            ),
            title: Text(
              'C-Net Delivery Partner',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: Text('Bihar Sharif service area'),
          ),
        ),
        const SizedBox(height: 12),
        const _Item(Icons.badge_outlined, 'Identity & KYC'),
        const _Item(Icons.two_wheeler_outlined, 'Vehicle details'),
        const _Item(
          Icons.account_balance_outlined,
          'Bank & settlement details',
        ),
        const _Item(Icons.support_agent, 'Delivery support'),
        const SizedBox(height: 12),
        OutlinedButton.icon(
          onPressed: () async {
            await const TokenStore().clear();
            if (!context.mounted) return;
            Navigator.of(context).pushAndRemoveUntil(
              MaterialPageRoute(builder: (_) => const LoginScreen()),
              (_) => false,
            );
          },
          icon: const Icon(Icons.logout),
          label: const Text('Logout'),
        ),
      ],
    ),
  );
}

class _Item extends StatelessWidget {
  const _Item(this.icon, this.title);
  final IconData icon;
  final String title;
  @override
  Widget build(BuildContext context) => Card(
    child: ListTile(
      leading: Icon(icon),
      title: Text(title),
      trailing: const Icon(Icons.chevron_right),
      onTap: () {},
    ),
  );
}
