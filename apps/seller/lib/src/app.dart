import 'package:flutter/material.dart';
import 'core/token_store.dart';
import 'screens/login_screen.dart';
import 'screens/main_shell.dart';
import 'theme/app_theme.dart';

class CNetStoreSellerApp extends StatelessWidget {
  const CNetStoreSellerApp({super.key});
  @override Widget build(BuildContext context) => MaterialApp(title: 'C-Net Store Seller', debugShowCheckedModeBanner: false, theme: AppTheme.light, home: FutureBuilder<bool>(future: const TokenStore().hasToken(), builder: (_, snapshot) => snapshot.connectionState != ConnectionState.done ? const Scaffold(body: Center(child: CircularProgressIndicator())) : snapshot.data == true ? const MainShell() : const LoginScreen()));
}

