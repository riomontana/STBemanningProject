package com.stbemanning.notifications;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.util.Log;

import com.stbemanning.api.Api;
import com.stbemanning.api.ApiListener;
import com.stbemanning.api.PerformNetworkRequest;

import org.json.JSONObject;

import java.util.HashMap;

public class SharedPrefManager implements ApiListener {

    private static final String SHARED_PREF_NAME = "FCMSharedPref";
    private static final String TAG_TOKEN = "tagtoken";
//    private boolean tagTokenAddedToDb = false;

    private static SharedPrefManager mInstance;
    private static Context mCtx;

    private SharedPrefManager(Context context) {
        mCtx = context;
    }

    public static synchronized SharedPrefManager getInstance(Context context) {
        if (mInstance == null) {
            mInstance = new SharedPrefManager(context);
        }
        return mInstance;
    }

    //this method will save the device token to shared preferences
    public boolean saveDeviceToken(String token){
        SharedPreferences sharedPreferences = mCtx.getSharedPreferences(SHARED_PREF_NAME, Context.MODE_PRIVATE);
        SharedPreferences.Editor editor = sharedPreferences.edit();
        editor.putString(TAG_TOKEN, token);
        editor.apply();
        return true;
    }

    //this method will fetch the device token from shared preferences
    public String getDeviceToken(){
        SharedPreferences sharedPreferences = mCtx.getSharedPreferences(SHARED_PREF_NAME, Context.MODE_PRIVATE);
        return  sharedPreferences.getString(TAG_TOKEN, null);
    }

//    public boolean tokenIsAddedToDb() {
//        Log.d("tokenIsGenerated", String.valueOf(tagTokenAddedToDb));
//        SharedPreferences sharedPreferences = mCtx.getSharedPreferences(SHARED_PREF_NAME, Context.MODE_PRIVATE);
//        if(sharedPreferences.getBoolean("TOKEN_ADDED_TO_DB", true)) {
//            tagTokenAddedToDb= true;
//        }
//        return(tagTokenAddedToDb);
//    }

    public void addTokenToDb(String token) {
        Log.d("","addTokenToDb");
        SharedPreferences sharedPreferences = PreferenceManager.getDefaultSharedPreferences(mCtx);
        int userId = sharedPreferences.getInt("USER_ID",0);
        Log.d("","user id =" + userId);

        if(userId != 0) {
            HashMap<String, String> params = new HashMap<>();

            params.put("token", token);
            params.put("user_id", String.valueOf(userId));

            PerformNetworkRequest request = new PerformNetworkRequest(Api.URL_ADD_APP_TOKEN, params, this);
            request.execute();
            Log.d("","Token added to db");
        }
    }

    public void updateTokenToDb(String token) {
        Log.d("","updateTokenToDb");
        SharedPreferences sharedPreferences = PreferenceManager.getDefaultSharedPreferences(mCtx);
        int userId = sharedPreferences.getInt("USER_ID",0);
        Log.d("","user id =" + userId);

        if(userId != 0) {
            HashMap<String, String> params = new HashMap<>();
            params.put("token", token);
            params.put("user_id", String.valueOf(userId));

            PerformNetworkRequest request = new PerformNetworkRequest(Api.URL_UPDATE_APP_TOKEN, params, this);
            request.execute();
            Log.d("","Token updated to db");
        }
    }

    @Override
    public void apiResponse(JSONObject response) {
        Log.d("json response", response.toString());
    }
}
