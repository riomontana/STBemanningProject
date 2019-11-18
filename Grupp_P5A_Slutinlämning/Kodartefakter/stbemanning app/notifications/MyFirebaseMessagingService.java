package com.stbemanning.notifications;

import android.content.Intent;
import android.util.Log;

import com.stbemanning.activities.CalendarActivity;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;
import org.json.JSONException;
import org.json.JSONObject;
public class MyFirebaseMessagingService extends FirebaseMessagingService {


    private static final String TAG = "MyFirebaseMsgService";

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        if (remoteMessage.getData().size() > 0) {
            Log.e(TAG, "Data Payload: " + remoteMessage.getData().toString());
            try {
                JSONObject json = new JSONObject(remoteMessage.getData().toString());
                sendPushNotification(json);
            } catch (Exception e) {
                Log.e(TAG, "Exception: " + e.getMessage());
            }
        }
    }

    //this method will display the notification
    //We are passing the JSONObject that is received from
    //firebase cloud messaging
    private void sendPushNotification(JSONObject json) {
        //optionally we can display the json into log
        Log.e(TAG, "Notification JSON " + json.toString());
        try {
            //getting the json data
            JSONObject data = json.getJSONObject("data");

            //parsing json data
            String title = data.getString("title");
            String message = data.getString("message");

            //creating MyNotificationManager object
            MyNotificationManager mNotificationManager = new MyNotificationManager(getApplicationContext());

            //creating an intent for the notification
//            Intent intent = new Intent(getApplicationContext(), SpecialWorkShiftActivity.class);

            Intent intent = new Intent(this, CalendarActivity.class);
//            TaskStackBuilder stackBuilder = TaskStackBuilder.create(this);
//
//            stackBuilder.addParentStack(CalendarActivity.class);
//            stackBuilder.addNextIntent(intent);
//            Intent intentSpecialWorkShifts= new Intent (this, SpecialWorkShiftActivity.class);
//            intentSpecialWorkShifts.putExtra("EmailId","you can Pass emailId here");
//            stackBuilder.addNextIntent(intentSpecialWorkShifts);
//            PendingIntent pendingIntent = stackBuilder.getPendingIntent(0, PendingIntent.FLAG_UPDATE_CURRENT);

            mNotificationManager.showSmallNotification(title, message, intent);


        } catch (JSONException e) {
            Log.e(TAG, "Json Exception: " + e.getMessage());
        } catch (Exception e) {
            Log.e(TAG, "Exception: " + e.getMessage());
        }
    }
}