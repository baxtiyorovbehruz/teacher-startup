package uz.tadbirkor.app

import android.annotation.SuppressLint
import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.view.View
import android.webkit.CookieManager
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import uz.tadbirkor.app.databinding.ActivityMainBinding

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null

    companion object {
        const val BASE_URL = "https://teacher-startup-1.onrender.com"
        const val BASE_HOST = "teacher-startup-1.onrender.com"
    }

    private val filePickerLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (fileUploadCallback == null) return@registerForActivityResult

        val results: Array<Uri>? = if (result.resultCode == RESULT_OK) {
            val data = result.data
            when {
                data?.clipData != null -> {
                    val count = data.clipData!!.itemCount
                    Array(count) { i -> data.clipData!!.getItemAt(i).uri }
                }
                data?.data != null -> arrayOf(data.data!!)
                else -> null
            }
        } else {
            null
        }

        fileUploadCallback?.onReceiveValue(results)
        fileUploadCallback = null
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Edge to Edge padding
        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { view, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            view.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }

        setupWebView()
        setupListeners()
        setupBackNavigation()

        val intentUrl = intent?.dataString
        val targetUrl = if (!intentUrl.isNullOrEmpty() && intentUrl.startsWith("http")) {
            intentUrl
        } else {
            BASE_URL
        }

        loadUrl(targetUrl)
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        val settings = binding.webView.settings
        settings.javaScriptEnabled = true
        settings.domStorageEnabled = true
        settings.databaseEnabled = true
        settings.useWideViewPort = true
        settings.loadWithOverviewMode = true
        settings.cacheMode = WebSettings.LOAD_DEFAULT
        settings.allowFileAccess = true

        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(binding.webView, true)

        binding.webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url ?: return false
                val urlString = url.toString()

                // Special schemes (Telegram, WhatsApp, tel, mailto)
                if (urlString.startsWith("tg:") || urlString.startsWith("https://t.me/") ||
                    urlString.startsWith("whatsapp:") || urlString.startsWith("https://wa.me/") ||
                    urlString.startsWith("tel:") || urlString.startsWith("mailto:")
                ) {
                    try {
                        val intent = Intent(Intent.ACTION_VIEW, url)
                        startActivity(intent)
                        return true
                    } catch (_: Exception) {
                        return false
                    }
                }

                // If external host, open in phone browser
                val host = url.host ?: ""
                if (!host.contains(BASE_HOST) && !host.contains("tadbirkor")) {
                    try {
                        val intent = Intent(Intent.ACTION_VIEW, url)
                        startActivity(intent)
                        return true
                    } catch (_: Exception) {
                        return false
                    }
                }

                return false
            }

            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                binding.progressBar.visibility = View.VISIBLE
                binding.progressBar.progress = 0
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                binding.progressBar.visibility = View.GONE
                binding.swipeRefreshLayout.isRefreshing = false
                showWebView()
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                if (request?.isForMainFrame == true) {
                    showOffline()
                }
            }
        }

        binding.webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                binding.progressBar.progress = newProgress
                if (newProgress >= 100) {
                    binding.progressBar.visibility = View.GONE
                } else {
                    binding.progressBar.visibility = View.VISIBLE
                }
            }

            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                fileUploadCallback?.onReceiveValue(null)
                fileUploadCallback = filePathCallback

                val intent = fileChooserParams?.createIntent() ?: Intent(Intent.ACTION_GET_CONTENT).apply {
                    type = "image/*"
                    addCategory(Intent.CATEGORY_OPENABLE)
                }

                try {
                    filePickerLauncher.launch(intent)
                } catch (_: Exception) {
                    fileUploadCallback = null
                    return false
                }
                return true
            }
        }
    }

    private fun setupListeners() {
        binding.swipeRefreshLayout.setOnRefreshListener {
            if (isNetworkAvailable()) {
                binding.webView.reload()
            } else {
                binding.swipeRefreshLayout.isRefreshing = false
                showOffline()
            }
        }

        // Only allow swipe-refresh when webView is scrolled to top
        binding.webView.viewTreeObserver.addOnScrollChangedListener {
            binding.swipeRefreshLayout.isEnabled = (binding.webView.scrollY == 0)
        }

        binding.btnRetry.setOnClickListener {
            if (isNetworkAvailable()) {
                showWebView()
                binding.webView.reload()
            }
        }
    }

    private fun setupBackNavigation() {
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (binding.webView.canGoBack()) {
                    binding.webView.goBack()
                } else {
                    finish()
                }
            }
        })
    }

    private fun loadUrl(url: String) {
        if (isNetworkAvailable()) {
            showWebView()
            binding.webView.loadUrl(url)
        } else {
            showOffline()
        }
    }

    private fun showWebView() {
        binding.layoutOffline.visibility = View.GONE
        binding.swipeRefreshLayout.visibility = View.VISIBLE
    }

    private fun showOffline() {
        binding.progressBar.visibility = View.GONE
        binding.swipeRefreshLayout.isRefreshing = false
        binding.swipeRefreshLayout.visibility = View.GONE
        binding.layoutOffline.visibility = View.VISIBLE
    }

    private fun isNetworkAvailable(): Boolean {
        val cm = getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        val network = cm.activeNetwork ?: return false
        val capabilities = cm.getNetworkCapabilities(network) ?: return false
        return capabilities.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    override fun onDestroy() {
        binding.webView.destroy()
        super.onDestroy()
    }
}
