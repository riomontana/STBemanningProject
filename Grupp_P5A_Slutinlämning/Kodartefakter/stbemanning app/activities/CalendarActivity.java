package com.stbemanning.activities;

import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.preference.PreferenceManager;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.MenuItem;
import android.widget.TextView;

import com.stbemanning.R;
import com.stbemanning.notifications.SharedPrefManager;
import com.stbemanning.api.Api;
import com.stbemanning.api.ApiListener;
import com.stbemanning.api.PerformNetworkRequest;
import com.stbemanning.model.User;
import com.stbemanning.model.WorkShift;
import com.github.sundeepk.compactcalendarview.CompactCalendarView;
import com.github.sundeepk.compactcalendarview.domain.Event;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;

/**
 * Main activity for viewing the work calendar etc.
 *
 * @author Alex Giang, Sanna Roengaard, Simon Borjesson,
 * Lukas Persson, Nikola Pajovic, Linus Forsberg
 */

public class CalendarActivity extends AppCompatActivity implements ApiListener {

    private CompactCalendarView compactCalendar;
    private SimpleDateFormat dateFormatMonth = new SimpleDateFormat("MMMM yyyy", Locale.getDefault());
    private SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd");
    private TextView tvInfo;
    private TextView tvMonthYear;
    private List<WorkShift> workShiftList;
    private List<Event> eventList;
    private User user;
    private int userId;
    private DrawerLayout mDrawerLayout;
    private NavigationView navigationView;
    private SharedPreferences prefs;
    private int mInterval = 5000;
    private Handler mHandler;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_calendar);

        if (getIntent().getBooleanExtra("EXIT", false)) {
            finish();
        }

        prefs = PreferenceManager.getDefaultSharedPreferences(this);
        userId = prefs.getInt("USER_ID", 0);
        tvMonthYear = (TextView) findViewById(R.id.tvMonthYear);
        tvInfo = (TextView) findViewById(R.id.tvInfo);
        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        ActionBar actionbar = getSupportActionBar();
        actionbar.setDisplayHomeAsUpEnabled(true);
        actionbar.setHomeAsUpIndicator(R.drawable.ic_menu_black_24dp);
        actionbar.setTitle(null);
        compactCalendar = (CompactCalendarView) findViewById(R.id.compactcalendar_view);
        compactCalendar.setUseThreeLetterAbbreviation(true);
        tvMonthYear.setText(dateFormatMonth.format(compactCalendar.getFirstDayOfCurrentMonth()));
        user = (User) getIntent().getSerializableExtra("user");
        checkIfTokenIsAdded();

        prefs.getString("USER_NAME", "");
        tvInfo.setText("Inloggad: " + prefs.getString("USER_NAME", ""));
        getWorkShifts(userId);
        mDrawerLayout = findViewById(R.id.drawer_layout);
        navigationView = findViewById(R.id.nav_view);

        // listener for side-menu
        navigationView.setNavigationItemSelectedListener(
                new NavigationView.OnNavigationItemSelectedListener() {
                    @Override
                    public boolean onNavigationItemSelected(MenuItem menuItem) {
                        menuItem.setChecked(true);
                        // close drawer when item is tapped
                        mDrawerLayout.closeDrawers();

                        switch (menuItem.getItemId()) {
                            case R.id.nav_special_workshift:
                                Intent intent = new Intent(CalendarActivity.this, SpecialWorkShiftActivity.class);
                                intent.putExtra("user", user);
                                startActivity(intent);
                                break;

                            case R.id.nav_info:
                                AlertDialog alertDialog = new AlertDialog.Builder(CalendarActivity.this).create();
                                alertDialog.setTitle("Information");
                                alertDialog.setIcon(R.mipmap.stsmall);
                                alertDialog.setMessage("* I kalendern kan du se dina inbokade arbetspass" +
                                        " som visas med mörkblå cirklar.\n" +
                                        "* Klicka på ett arbetspass för att se företag och tid.\n" +
                                        "* Swipa höger eller vänster i kalendern för att byta månad.\n" +
                                        "* Om du har förfrågningar om specialpass kan du se dem under Specialpass i menyn\n" +
                                        "* Har du synpunkter om appen eller upptäcker något som inte fungerar som det ska, vänligen kontakta oss.");
                                alertDialog.setButton(AlertDialog.BUTTON_NEGATIVE, "OK",
                                        new DialogInterface.OnClickListener() {
                                            public void onClick(DialogInterface dialog, int which) {
                                                dialog.dismiss();
                                            }
                                        });
                                alertDialog.show();
                                break;

                            case R.id.nav_weblink:
                                Intent browserIntent = new Intent(Intent.ACTION_VIEW,
                                        Uri.parse("https://stbemanning.com/index.php"));
                                startActivity(browserIntent);
                                break;

                            case R.id.nav_logout:
                                AlertDialog.Builder adb = new AlertDialog.Builder(CalendarActivity.this);
                                adb.setTitle("Är du säker på att du vill logga ut?");
                                adb.setPositiveButton("Logga ut", new DialogInterface.OnClickListener() {
                                    public void onClick(DialogInterface dialog, int which) {
                                        SharedPreferences.Editor prefEditor =
                                                PreferenceManager.getDefaultSharedPreferences(getApplicationContext()).edit();
                                        prefEditor.clear();
                                        prefEditor.apply();
                                        finishAffinity();
                                        startActivity(new Intent(CalendarActivity.this, LoginActivity.class));
                                    }
                                });
                                adb.setNegativeButton("Avbryt", new DialogInterface.OnClickListener() {
                                    public void onClick(DialogInterface dialog, int which) {
                                        dialog.dismiss();
                                    }
                                });
                                adb.show();

                                break;
                        }
                        return true;
                    }
                });
        logDeviceToken();
        mHandler = new Handler();
        startRepeatingTask();
    }

    private void setCalenderListeners() {
        Log.d("setCalendar","setCalendar");
        compactCalendar.setListener(new CompactCalendarView.CompactCalendarViewListener() {
            @Override
            public void onDayClick(Date dateClicked) {
                for (int i = 0; i < eventList.size(); i++) {
                    Event event = eventList.get(i);
                    Date dateEvent = new Date(event.getTimeInMillis());
                    Log.d("date clicked", dateClicked.toString());
                    Log.d("event time", dateEvent.toString());

                    if (dateClicked.getTime() == event.getTimeInMillis()) {
                        WorkShift workShift = workShiftList.get(i);
                        showWorkShiftDialog(workShift);
                    }
                }
            }
            @Override
            public void onMonthScroll(Date firstDayOfNewMonth) {
                tvMonthYear.setText(dateFormatMonth.format(firstDayOfNewMonth));
            }
        });
    }

    private void startRepeatingTask() {
        mStatusChecker.run();
    }

    private void stopRepeatingTask() {
        mHandler.removeCallbacks(mStatusChecker);
    }

    private void checkIfTokenIsAdded() {
        String currentToken = SharedPrefManager.getInstance(this).getDeviceToken();
        SharedPreferences.Editor prefEditor =
                PreferenceManager.getDefaultSharedPreferences(this).edit();
        String tokenSavedOnDb;

        if (prefs.contains("TOKEN_SAVED_ON_DB")) {
            tokenSavedOnDb = prefs.getString("TOKEN_SAVED_ON_DB", "");
            if (currentToken.matches(tokenSavedOnDb)) {
                Log.d("CAL:checkIfTokenAdded", "no update needed");
            } else {
                SharedPrefManager.getInstance(this).updateTokenToDb(currentToken);
                Log.d("CAL:checkIfTokenAdded", "token is updated");
                prefEditor.putString("TOKEN_SAVED_ON_DB", currentToken);
                prefEditor.apply();
            }
        } else {
            HashMap<String, String> params = new HashMap<>();
            params.put("user_id", String.valueOf(user.getUserId()));
            PerformNetworkRequest request = new PerformNetworkRequest(Api.URL_DELETE_APP_TOKEN, params, this);
            request.execute();
            SharedPrefManager.getInstance(this).addTokenToDb(currentToken);
            prefEditor.putString("TOKEN_SAVED_ON_DB", currentToken);
            prefEditor.apply();
            Log.d("CAL:checkIfTokenAdded", "token is added");

        }
    }

    /**
     * Opens side menu on click on navigation button
     *
     * @param item
     * @return
     */
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case android.R.id.home:
                mDrawerLayout.openDrawer(GravityCompat.START);
                navigationView.bringToFront();
                mDrawerLayout.requestLayout();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private void showWorkShiftDialog(WorkShift workShift) {
        String[] splitDateTimeStart = workShift.getShiftStart().split(" ");
        String[] splitDateTimeEnd = workShift.getShiftEnd().split(" ");
        AlertDialog.Builder adb = new AlertDialog.Builder(this);
        adb.setIcon(R.mipmap.stsmall);
        adb.setTitle("Arbetspass \n")
                .setMessage("Företag: " + workShift.getCompany() +
                        "\nDatum: " + splitDateTimeStart[0] +
                        "\nBörjar: " + splitDateTimeStart[1] +
                        "\nSlutar: " + splitDateTimeEnd[1]);
        adb.setPositiveButton("OK", new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
            }
        });
        adb.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        adb.show();
    }

    public void getWorkShifts(int userId) {
        HashMap<String, String> params = new HashMap<>();

        params.put("user_id", String.valueOf(userId));

        PerformNetworkRequest request = new PerformNetworkRequest(Api.URL_GET_WORK_SHIFTS, params, this);
        request.execute();
    }

    @Override
    public void apiResponse(JSONObject response) {
        workShiftList = new ArrayList<>();
        Log.d("json ", response.toString());
        try {
            JSONArray jsonArray = response.getJSONArray("work_shifts");

            for (int i = 0; i < jsonArray.length(); i++) {
                JSONObject jsonObject = jsonArray.getJSONObject(i);
                int workShiftId = jsonObject.getInt("work_shift_id");
                String shiftStart = jsonObject.getString("shift_start");
                String shiftEnd = jsonObject.getString("shift_end");
                String company = jsonObject.getString("customer_name");
                WorkShift workShift = new WorkShift(workShiftId, shiftStart, shiftEnd, company);
                workShiftList.add(workShift);
            }
            addWorkShiftsToCalender();
            setCalenderListeners();
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    private void addWorkShiftsToCalender() {
        compactCalendar.removeAllEvents();
        eventList = new ArrayList<>();
        for (WorkShift workShift : workShiftList) {
            String[] splitDateTime = workShift.getShiftStart().split(" ");
            Event event = new Event(R.color.colorAccent, convertDate(splitDateTime[0]), "work shift");
            eventList.add(event);
            compactCalendar.addEvent(event);
        }
    }

    private long convertDate(String shiftStart) {
        long convertedDate = 0;
        try {
            Date date = sdf.parse(shiftStart);
            convertedDate = date.getTime();
        } catch (ParseException e) {
            e.printStackTrace();
        }
        return convertedDate;
    }

    /**
     * Disable back button
     */
    @Override
    public void onBackPressed() {
        super.onBackPressed();
        Intent intent = new Intent(CalendarActivity.this, CalendarActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        intent.putExtra("EXIT", true);
        startActivity(intent);
    }

    public void logDeviceToken() {
        String token = SharedPrefManager.getInstance(this).getDeviceToken();

        //if token is not null
        if (token != null) {
            //displaying the token
            Log.d("Device token ", token);
        } else {
            //if token is null that means something wrong
            Log.d("", "Token not generated");
        }
    }

    public void onRestart() {
        super.onRestart();
        finish();
        startActivity(getIntent());
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        stopRepeatingTask();
    }

    Runnable mStatusChecker = new Runnable() {
        @Override
        public void run() {
            try {
                getWorkShifts(userId);
            } finally {
                mHandler.postDelayed(mStatusChecker, mInterval);
            }
        }
    };

}
