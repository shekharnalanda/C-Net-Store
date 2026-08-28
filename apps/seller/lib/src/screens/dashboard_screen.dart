import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../theme/app_theme.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Seller Dashboard',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          Text('C-Net Store Partner', style: TextStyle(fontSize: 12)),
        ],
      ),
      actions: const [
        IconButton(onPressed: null, icon: Icon(Icons.notifications_none)),
      ],
    ),
    body: RefreshIndicator(
      onRefresh: () async {},
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          FutureBuilder<Map<String, dynamic>>(
            future: ApiClient().businesses(),
            builder: (_, snapshot) {
              final response = snapshot.data ?? <String, dynamic>{};
              final payload = response['data'];
              final businesses = payload is List<dynamic>
                  ? payload
                  : payload is Map<String, dynamic>
                  ? payload['data'] as List<dynamic>? ?? <dynamic>[]
                  : <dynamic>[];
              final status = businesses.isEmpty
                  ? 'Registration required'
                  : businesses.first['status']?.toString() ?? 'pending';
              return _Approval(status: status);
            },
          ),
          const SizedBox(height: 14),
          const Row(
            children: [
              Expanded(
                child: _Metric(
                  label: 'Today Orders',
                  value: '0',
                  icon: Icons.shopping_bag_outlined,
                  color: AppColors.blue,
                ),
              ),
              SizedBox(width: 10),
              Expanded(
                child: _Metric(
                  label: 'Revenue',
                  value: '₹0',
                  icon: Icons.currency_rupee,
                  color: AppColors.green,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Row(
            children: [
              Expanded(
                child: _Metric(
                  label: 'Products',
                  value: '0',
                  icon: Icons.inventory_2_outlined,
                  color: AppColors.orange,
                ),
              ),
              SizedBox(width: 10),
              Expanded(
                child: _Metric(
                  label: 'Low Stock',
                  value: '0',
                  icon: Icons.warning_amber,
                  color: Colors.red,
                ),
              ),
            ],
          ),
          const SizedBox(height: 22),
          Text(
            'Quick actions',
            style: Theme.of(context).textTheme.titleLarge
                ?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 10),
          const Card(
            child: Column(
              children: [
                ListTile(
                  leading: Icon(Icons.add_box_outlined),
                  title: Text('Add new product'),
                  trailing: Icon(Icons.chevron_right),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.inventory_outlined),
                  title: Text('Update inventory'),
                  trailing: Icon(Icons.chevron_right),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.schedule),
                  title: Text('Store opening hours'),
                  trailing: Icon(Icons.chevron_right),
                ),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}

class _Approval extends StatelessWidget {
  const _Approval({required this.status});
  final String status;
  @override
  Widget build(BuildContext context) {
    final approved = status == 'approved';
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: approved
            ? AppColors.green.withValues(alpha: .1)
            : AppColors.orange.withValues(alpha: .12),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Icon(
            approved ? Icons.verified : Icons.hourglass_top,
            color: approved ? AppColors.green : AppColors.orange,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  approved ? 'Business approved' : 'Approval status: $status',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                Text(
                  approved
                      ? 'Your store is visible to customers.'
                      : 'Products go live after C-Net approval.',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Metric extends StatelessWidget {
  const _Metric({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });
  final String label, value;
  final IconData icon;
  final Color color;
  @override
  Widget build(BuildContext context) => Card(
    child: Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color),
          const SizedBox(height: 12),
          Text(
            value,
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
          ),
          Text(label, style: Theme.of(context).textTheme.bodySmall),
        ],
      ),
    ),
  );
}
