import 'package:flutter/material.dart';

import 'account_screen.dart';
import 'cart_screen.dart';
import 'catalog_screen.dart';
import 'home_screen.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});
  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;
  int _cartVersion = 0;

  void _cartChanged() => setState(() => _cartVersion++);

  @override
  Widget build(BuildContext context) {
    final pages = [
      const HomeScreen(),
      CatalogScreen(onCartChanged: _cartChanged),
      CartScreen(version: _cartVersion, onCartChanged: _cartChanged),
      const AccountScreen(),
    ];

    return Scaffold(
    body: IndexedStack(index: _index, children: pages),
    bottomNavigationBar: NavigationBar(
      selectedIndex: _index,
      onDestinationSelected: (value) => setState(() => _index = value),
      destinations: const [
        NavigationDestination(
          icon: Icon(Icons.home_outlined),
          selectedIcon: Icon(Icons.home),
          label: 'Home',
        ),
        NavigationDestination(
          icon: Icon(Icons.grid_view_outlined),
          selectedIcon: Icon(Icons.grid_view),
          label: 'Categories',
        ),
        NavigationDestination(
          icon: Badge(child: Icon(Icons.shopping_cart_outlined)),
          selectedIcon: Badge(child: Icon(Icons.shopping_cart)),
          label: 'Cart',
        ),
        NavigationDestination(
          icon: Icon(Icons.person_outline),
          selectedIcon: Icon(Icons.person),
          label: 'Account',
        ),
      ],
    ),
  );
  }
}
