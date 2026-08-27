import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';

import '../core/api_client.dart';
import '../theme/app_theme.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  bool online = false, busy = false;
  Future<void> toggle(bool value) async {
    setState(() => busy = true);
    try {
      Position? position;
      if (value) {
        var permission = await Geolocator.checkPermission();
        if (permission == LocationPermission.denied)
          permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied ||
            permission == LocationPermission.deniedForever)
          throw Exception('Location permission required');
        position = await Geolocator.getCurrentPosition();
      }
      await ApiClient().availability(
        value,
        latitude: position?.latitude,
        longitude: position?.longitude,
      );
      setState(() => online = value);
    } catch (_) {
      if (mounted)
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'Online status update नहीं हो सका। Location permission जाँचें।',
            ),
          ),
        );
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text(
        'Delivery Dashboard',
        style: TextStyle(fontWeight: FontWeight.bold),
      ),
      actions: const [
        IconButton(onPressed: null, icon: Icon(Icons.notifications_none)),
      ],
    ),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        FutureBuilder<Map<String, dynamic>>(
          future: ApiClient().profile(),
          builder: (_, snapshot) {
            final data = snapshot.data?['data'] as Map<String, dynamic>?;
            final status = data?['status']?.toString() ?? 'pending';
            return Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: status == 'approved'
                    ? AppColors.green.withValues(alpha: .1)
                    : AppColors.orange.withValues(alpha: .12),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  Icon(
                    status == 'approved' ? Icons.verified : Icons.hourglass_top,
                    color: status == 'approved'
                        ? AppColors.green
                        : AppColors.orange,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      status == 'approved'
                          ? 'Approved delivery partner'
                          : 'Approval status: $status',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
        const SizedBox(height: 14),
        Card(
          child: SwitchListTile(
            value: online,
            onChanged: busy ? null : toggle,
            secondary: Icon(
              online ? Icons.location_on : Icons.location_off,
              color: online ? AppColors.green : Colors.grey,
            ),
            title: Text(
              online ? 'You are Online' : 'You are Offline',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: Text(
              online
                  ? 'New delivery requests can be assigned.'
                  : 'Go online to receive deliveries.',
            ),
          ),
        ),
        const SizedBox(height: 14),
        const Row(
          children: [
            Expanded(
              child: _Metric(
                'Today Deliveries',
                '0',
                Icons.delivery_dining,
                AppColors.blue,
              ),
            ),
            SizedBox(width: 10),
            Expanded(
              child: _Metric(
                'Today Earnings',
                '₹0',
                Icons.currency_rupee,
                AppColors.green,
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),
        Text(
          'Current assignment',
          style: Theme.of(context).textTheme.titleLarge
              ?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 10),
        const Card(
          child: Padding(
            padding: EdgeInsets.all(22),
            child: Row(
              children: [
                Icon(Icons.route_outlined, size: 40),
                SizedBox(width: 14),
                Expanded(child: Text('No active delivery assignment.')),
              ],
            ),
          ),
        ),
      ],
    ),
  );
}

class _Metric extends StatelessWidget {
  const _Metric(this.label, this.value, this.icon, this.color);
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
            style: const TextStyle(fontSize: 21, fontWeight: FontWeight.bold),
          ),
          Text(label, style: Theme.of(context).textTheme.bodySmall),
        ],
      ),
    ),
  );
}
